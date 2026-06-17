<?php
require_once '../../config/database.php';

// Check if student is logged in
if (!isset($_SESSION['siswa_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get student data
$siswa_id = $_SESSION['siswa_id'];
$today = date('Y-m-d');

// Get student attendance summary - ADD APPROVAL STATUS FILTER
$sql = "SELECT 
            COUNT(CASE WHEN status = 'Hadir' AND approval_status = 'Approved' THEN 1 END) as hadir,
            COUNT(CASE WHEN status = 'Sakit' AND approval_status = 'Approved' THEN 1 END) as sakit,
            COUNT(CASE WHEN status = 'Izin' AND approval_status = 'Approved' THEN 1 END) as izin,
            COUNT(CASE WHEN status = 'Terlambat' AND approval_status = 'Approved' THEN 1 END) as terlambat,
            COUNT(CASE WHEN status = 'Alpha' AND approval_status = 'Approved' THEN 1 END) as alpha
        FROM absensi 
        WHERE siswa_id = :siswa_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['siswa_id' => $siswa_id]);
$attendance_summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if student has submitted attendance today (modified to exclude rejected submissions)
$sql = "SELECT * FROM absensi 
        WHERE siswa_id = :siswa_id 
        AND tanggal = :today 
        AND approval_status != 'Rejected'";
$stmt = $conn->prepare($sql);
$stmt->execute(['siswa_id' => $siswa_id, 'today' => $today]);
$today_attendance = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if student has a rejected submission for today
$sql = "SELECT * FROM absensi 
        WHERE siswa_id = :siswa_id 
        AND tanggal = :today 
        AND approval_status = 'Rejected'";
$stmt = $conn->prepare($sql);
$stmt->execute(['siswa_id' => $siswa_id, 'today' => $today]);
$rejected_attendance = $stmt->fetch(PDO::FETCH_ASSOC);

// Get attendance history for the current month - ADD APPROVAL STATUS FILTER
$sql = "SELECT 
            a.*, 
            DATE_FORMAT(a.tanggal, '%d') as day,
            DATE_FORMAT(a.tanggal, '%a') as day_name
        FROM absensi a
        WHERE a.siswa_id = :siswa_id 
        AND MONTH(a.tanggal) = MONTH(CURRENT_DATE())
        AND YEAR(a.tanggal) = YEAR(CURRENT_DATE())
        AND a.approval_status = 'Approved'
        ORDER BY a.tanggal DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['siswa_id' => $siswa_id]);
$attendance_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending approval requests
$sql = "SELECT * FROM absensi 
        WHERE siswa_id = :siswa_id 
        AND approval_status = 'Pending'
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['siswa_id' => $siswa_id]);
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate attendance percentage - ONLY COUNT APPROVED RECORDS
$total_days = count($attendance_history);
$present_days = $attendance_summary['hadir'] + $attendance_summary['terlambat'];
$attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100) : 0;

// Process attendance submission
$submission_message = '';
$submission_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    // VALIDATE IP ADDRESS FIRST
    try {
        validate_attendance_ip();
    } catch (Exception $e) {
        $submission_status = 'error';
        $submission_message = $e->getMessage();
        // Skip the rest of the submission process
        goto skip_submission;
    }
    
    // Status will be determined by admin, set to pending for now
    $status = 'Menunggu'; // Default status, admin will change this
    $keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';

    // Check if attendance already submitted (exclude rejected ones)
    if ($today_attendance) {
        $submission_status = 'error';
        $submission_message = 'Anda sudah melakukan absensi hari ini.';
    } else {
        // If resubmitting after rejection, delete the rejected submission first
        if ($rejected_attendance) {
            $delete_sql = "DELETE FROM absensi WHERE id = :id";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->execute(['id' => $rejected_attendance['id']]);
        }



        // If no error, save attendance
        if ($submission_status !== 'error') {
            try {
                $conn->beginTransaction();

                // All attendance submissions require admin approval
                $approval_status = 'Pending';

                // No jam_masuk since status is determined by admin
                $jam_masuk = null;

                // Process file upload for bukti (optional)
                $bukti_foto = null;
                if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../../uploads/bukti/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $filename = 'bukti_' . $siswa_id . '_' . date('Ymd_His') . '_' . basename($_FILES['bukti_foto']['name']);
                    $target_file = $upload_dir . $filename;

                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($_FILES['bukti_foto']['type'], $allowed_types)) {
                        throw new Exception("Tipe file tidak diizinkan. Hanya gambar yang diperbolehkan.");
                    }

                    if ($_FILES['bukti_foto']['size'] > 5 * 1024 * 1024) { // 5MB limit
                        throw new Exception("Ukuran file terlalu besar. Maksimal 5MB.");
                    }

                    if (!move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $target_file)) {
                        throw new Exception("Gagal mengupload file.");
                    }

                    $bukti_foto = 'uploads/bukti/' . $filename;
                }

                // Process camera image data (base64)
                if (isset($_POST['camera_image_data']) && !empty($_POST['camera_image_data'])) {
                    $upload_dir = '../../uploads/bukti/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Decode base64 image data
                    $img_data = $_POST['camera_image_data'];
                    if (strpos($img_data, 'data:image/') === 0) {
                        $img_data = substr($img_data, strpos($img_data, ',') + 1);
                        $decoded_data = base64_decode($img_data);

                        // Determine file extension from data URL
                        $data_url = $_POST['camera_image_data'];
                        $type = explode(';', explode(':', $data_url)[1])[0];
                        $extension = 'jpg'; // default
                        if ($type === 'image/png') $extension = 'png';
                        elseif ($type === 'image/gif') $extension = 'gif';
                        elseif ($type === 'image/webp') $extension = 'webp';

                        // Generate filename
                        $filename = 'camera_' . $siswa_id . '_' . date('Ymd_His') . '.' . $extension;
                        $target_file = $upload_dir . $filename;

                        // Save the decoded image
                        if (file_put_contents($target_file, $decoded_data)) {
                            $bukti_foto = 'uploads/bukti/' . $filename;
                        }
                    }
                }

                // Get IP address and network info
                $ip_address = get_client_ip();
                $network_info = isset($_POST['network_info']) ? $_POST['network_info'] : null;
                $location_data = isset($_POST['location_data']) ? $_POST['location_data'] : null;

                // Insert the attendance record
                $sql = "INSERT INTO absensi (siswa_id, tanggal, jam_masuk, status, keterangan, bukti_foto, approval_status, ip_address, network_info, location_data, created_at)
                        VALUES (:siswa_id, :tanggal, :jam_masuk, :status, :keterangan, :bukti_foto, :approval_status, :ip_address, :network_info, :location_data, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'siswa_id' => $siswa_id,
                    'tanggal' => $today,
                    'jam_masuk' => $jam_masuk,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'bukti_foto' => $bukti_foto,
                    'approval_status' => $approval_status,
                    'ip_address' => $ip_address,
                    'network_info' => $network_info,
                    'location_data' => $location_data
                ]);

                // Add activity log
                $approval_text = $approval_status === 'Approved' ? ' (disetujui otomatis)' : ' (menunggu persetujuan admin)';
                $log_sql = "INSERT INTO activity_log (user_type, user_id, activity_type, description)
                            VALUES ('siswa', :user_id, 'absensi', :description)";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->execute([
                    'user_id' => $siswa_id,
                    'description' => "Siswa {$_SESSION['siswa_name']} mengajukan absensi sebagai {$status}{$approval_text}"
                ]);

                $conn->commit();

                $submission_status = 'success';
                $submission_message = 'Absensi berhasil dikirim dan menunggu persetujuan.';

                // Refresh the page to show the updated attendance
                header("Location: index.php?status=success&message=" . urlencode($submission_message));
                exit();
            } catch (Exception $e) {
                $conn->rollBack();
                $submission_status = 'error';
                $submission_message = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
    
    // Label for skipping submission when IP validation fails
    skip_submission:
}

// Handle cancellation of pending requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_request'])) {
    $request_id = $_POST['request_id'];

    try {
        $conn->beginTransaction();

        // First verify that this request belongs to the current student
        $check_sql = "SELECT * FROM absensi WHERE id = :id AND siswa_id = :siswa_id AND approval_status = 'Pending'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([
            'id' => $request_id,
            'siswa_id' => $siswa_id
        ]);

        if ($check_stmt->rowCount() > 0) {
            // Delete the pending request
            $delete_sql = "DELETE FROM absensi WHERE id = :id";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->execute(['id' => $request_id]);

            // Add log entry
            $log_sql = "INSERT INTO activity_log (user_type, user_id, activity_type, description)
                       VALUES ('siswa', :user_id, 'delete', :description)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->execute([
                'user_id' => $siswa_id,
                'description' => "Siswa {$_SESSION['siswa_name']} membatalkan pengajuan absensi"
            ]);

            $conn->commit();

            // Refresh page to show updated list
            header("Location: index.php?status=success&message=Permintaan berhasil dibatalkan");
            exit();
        } else {
            throw new Exception("Permintaan tidak ditemukan atau bukan milik Anda");
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $submission_status = 'error';
        $submission_message = 'Gagal membatalkan permintaan: ' . $e->getMessage();
    }
}

// Check for URL parameters (for redirects)
if (isset($_GET['status']) && isset($_GET['message'])) {
    $submission_status = $_GET['status'];
    $submission_message = $_GET['message'];
}

// Get attendance history by status for pie chart - ADD APPROVAL STATUS FILTER
$sql = "SELECT status, COUNT(*) as count FROM absensi 
        WHERE siswa_id = :siswa_id 
        AND approval_status = 'Approved'
        GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->execute(['siswa_id' => $siswa_id]);
$attendance_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the data for the chart
$chart_data = [
    'labels' => [],
    'data' => [],
    'colors' => []
];

$status_colors = [
    'Hadir' => '#10B981',
    'Sakit' => '#EAB308',
    'Izin' => '#8B5CF6',
    'Terlambat' => '#F97316',
    'Alpha' => '#EF4444'
];

foreach ($attendance_stats as $stat) {
    $chart_data['labels'][] = $stat['status'];
    $chart_data['data'][] = (int)$stat['count'];
    $chart_data['colors'][] = $status_colors[$stat['status']] ?? '#9CA3AF';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - SMKN 1 Sanden</title>    
    <!-- Apply saved theme immediately to prevent flash -->
    <script>
        (function() {
            const theme = localStorage.getItem('app-theme');
            if (theme === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .glass-effect {
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(20, 184, 166, 0.2);
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.8), 0 2px 4px rgba(0, 0, 0, 0.5);
            border-radius: 4px !important;
        }

        .menu-active {
            background: linear-gradient(to right, rgba(20, 184, 166, 0.15), rgba(20, 184, 166, 0.02));
            border-left: 3px solid #14b8a6;
            color: #fff;
            text-shadow: 0 0 8px rgba(20, 184, 166, 0.4);
        }

        /* Mobile menu active */
        .mobile-menu-active {
            background: rgba(20, 184, 166, 0.2);
            border-bottom: 2px solid #14b8a6;
        }

        body {
            background: #0f0f13;
            color: #d1d5db;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-out forwards;
        }

        .status-hadir {
            --status-color: #10B981;
        }

        .status-sakit {
            --status-color: #EAB308;
        }

        .status-izin {
            --status-color: #8B5CF6;
        }

        .status-terlambat {
            --status-color: #F97316;
        }

        .status-alpha {
            --status-color: #EF4444;
        }

        .status-badge {
            background-color: rgba(var(--tw-color-primary-500), 0.1);
            color: var(--status-color);
            border: 1px solid rgba(var(--tw-color-primary-500), 0.2);
        }

        /* Sidebar transition */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        /* Mobile overlay */
        .mobile-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            transition: opacity 0.3s ease-in-out;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        /* Modal responsive adjustments */
        @media (max-width: 640px) {
            #imageModal img {
                max-height: 80vh;
            }

            #confirmationModal {
                padding: 1rem;
                margin: 0 1rem;
            }
        }

        /* Better focus styles for accessibility */
        button:focus,
        a:focus {
            outline: 2px solid rgba(20, 184, 166, 0.5);
            outline-offset: 2px;
        }

        /* Smooth transitions for all interactive elements */
        button,
        a,
        input,
        select,
        textarea {
            transition: all 0.2s ease;
        }
    </style>
</head>

<body class="min-h-screen text-white bg-fixed">
    <!-- Mobile Overlay - only visible when sidebar is open on mobile -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>

    <!-- Side Navigation -->
    <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 glass-effect border-r border-teal-500/20 z-50 sidebar-transition -translate-x-full lg:translate-x-0">
        <div class="flex items-center justify-between p-4 lg:p-6 border-b border-teal-500/20">
            <div class="flex items-center gap-3">
                <img src="../../assets/default/smkn1sanden.png" alt="SMKN 1 Sanden" class="h-8 lg:h-10 w-auto">
                <div>
                    <h1 class="font-semibold text-sm lg:text-base">SMKN 1 Sanden</h1>
                    <p class="text-xs text-gray-400">Sistem Absensi</p>
                </div>
            </div>
            <!-- Close sidebar button - only visible on mobile -->
            <button class="text-gray-400 hover:text-white lg:hidden" onclick="toggleSidebar()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-4 border-b border-teal-500/20">
            <div class="flex items-center gap-3">
                <img src="../../<?= $_SESSION['siswa_photo'] ?: 'assets/default/photo-profile.png' ?>" alt="Profile" class="h-10 w-10 rounded-full object-cover border-2 border-teal-500/30">
                <div>
                    <h2 class="font-medium text-sm"><?= $_SESSION['siswa_name'] ?></h2>
                    <p class="text-xs text-gray-400"><?= $_SESSION['siswa_kelas'] ?> <?= $_SESSION['siswa_jurusan'] ?></p>
                </div>
            </div>
        </div>

        <nav class="p-4 space-y-2 overflow-y-auto no-scrollbar" style="max-height: calc(100vh - 160px);">
            <a href="index.php" class="flex items-center gap-3 text-white/90 p-3 rounded-lg menu-active">
                <i class="fas fa-home text-teal-400"></i>
                <span>Dashboard</span>
            </a>
            <a href="../riwayat/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-history"></i>
                <span>Riwayat Absensi</span>
            </a>
            <a href="../profil/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>

            <hr class="border-gray-700/50 my-4">

            <!-- Theme Toggle -->
            <button id="theme-toggle-btn" onclick="toggleTheme()" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-400/10 transition-colors w-full text-left">
                <i class="fas fa-sun text-yellow-400"></i>
                <span>Mode Terang</span>
            </button>

            <div class="pt-4 mt-auto">
                <a href="../logout.php" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-red-500/10 hover:text-red-500 transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen bg-transparent transition-all duration-300">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-gray-900/60 backdrop-blur-lg sticky top-0 z-30 px-4 py-3 flex items-center justify-between border-b border-teal-500/20">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="text-white p-1">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <img src="../../assets/default/smkn1sanden.png" alt="SMKN 1 Sanden" class="h-8 w-auto">
            </div>
            <div class="flex items-center gap-3">
                <span id="current-time-mobile" class="text-sm font-medium"></span>
                <img src="../../<?= $_SESSION['siswa_photo'] ?: 'assets/default/photo-profile.png' ?>" alt="Profile" class="h-8 w-8 rounded-full object-cover border border-teal-500/30">
            </div>
        </div>

        <!-- Content Body -->
        <div class="p-4 lg:p-8">
            <div class="max-w-6xl mx-auto">
                <!-- Header with greeting and date -->
                <header class="mb-6 lg:mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 class="text-xl lg:text-2xl font-bold">Selamat Datang, <?= explode(' ', $_SESSION['siswa_name'])[0] ?>!</h1>
                            <p class="text-gray-400 text-sm lg:text-base mt-1"><?= date('l, d F Y') ?></p>
                        </div>

                        <!-- Current Time - Desktop Only -->
                        <div class="hidden lg:flex glass-effect rounded-lg px-4 py-2 items-center gap-2 mt-4 md:mt-0">
                            <i class="fas fa-clock text-teal-300"></i>
                            <span id="current-time" class="font-medium"></span>
                        </div>
                    </div>
                </header>

                <?php if ($submission_message): ?>
                    <div class="mb-6 p-4 rounded-lg border <?= $submission_status === 'success' ? 'bg-green-500/10 border-green-500/30 text-green-500' : 'bg-red-500/10 border-red-500/30 text-red-500' ?>">
                        <div class="flex items-center">
                            <i class="fas <?= $submission_status === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3"></i>
                            <p class="text-sm"><?= $submission_message ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-6 lg:mb-8">
                    <!-- Today's Attendance Form -->
                    <div class="glass-effect rounded-xl p-4 lg:p-6 lg:col-span-2">
                        <h3 class="font-semibold text-base lg:text-lg mb-4 flex items-center">
                            <i class="fas fa-clipboard-check text-teal-400 mr-2"></i>
                            Absensi Hari Ini
                        </h3>

                        <?php if ($today_attendance): ?>
                            <!-- Already submitted attendance -->
                            <div class="p-3 lg:p-4 border border-gray-700/50 rounded-lg bg-gray-800/50">
                                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                                    <div>
                                        <div class="mb-3 lg:mb-4 flex items-center flex-wrap gap-2">
                                            <span class="text-sm font-medium mr-2">Status:</span>
                                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                            <?php
                                            switch ($today_attendance['status']) {
                                                case 'Hadir':
                                                    echo 'bg-green-500/10 text-green-500 border border-green-500/30';
                                                    break;
                                                case 'Sakit':
                                                    echo 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/30';
                                                    break;
                                                case 'Izin':
                                                    echo 'bg-teal-500/10 text-teal-400 border border-teal-500/30';
                                                    break;
                                                case 'Terlambat':
                                                    echo 'bg-orange-500/10 text-orange-500 border border-orange-500/30';
                                                    break;
                                                case 'Alpha':
                                                    echo 'bg-red-500/10 text-red-500 border border-red-500/30';
                                                    break;
                                                default:
                                                    echo 'bg-gray-500/10 text-gray-500 border border-gray-500/30';
                                            }
                                            ?>">
                                                <?= $today_attendance['status'] ?>
                                            </span>
                                        </div>

                                        <div class="mb-3 lg:mb-4">
                                            <span class="text-sm font-medium">Approval Status:</span>
                                            <span class="px-3 py-1 rounded-full text-xs font-medium ml-2
                                            <?php
                                            switch ($today_attendance['approval_status']) {
                                                case 'Approved':
                                                    echo 'bg-green-500/10 text-green-500 border border-green-500/30';
                                                    break;
                                                case 'Rejected':
                                                    echo 'bg-red-500/10 text-red-500 border border-red-500/30';
                                                    break;
                                                default:
                                                    echo 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/30';
                                            }
                                            ?>">
                                                <?= $today_attendance['approval_status'] ?>
                                            </span>
                                        </div>

                                        <?php if ($today_attendance['jam_masuk']): ?>
                                            <div class="mb-3 lg:mb-4">
                                                <span class="text-sm font-medium">Waktu Absen:</span>
                                                <span class="text-gray-300 ml-2"><?= date('H:i', strtotime($today_attendance['jam_masuk'])) ?> WIB</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($today_attendance['keterangan']): ?>
                                            <div class="mt-4">
                                                <span class="text-sm font-medium block mb-1">Keterangan:</span>
                                                <p class="text-gray-400 text-sm bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                                                    <?= htmlspecialchars($today_attendance['keterangan']) ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($today_attendance['bukti_foto']): ?>
                                        <div class="sm:ml-4 shrink-0">
                                            <span class="text-sm font-medium block mb-1">Bukti:</span>
                                            <img src="../../<?= $today_attendance['bukti_foto'] ?>" alt="Bukti"
                                                class="w-24 h-24 object-cover rounded-lg border border-gray-700/50"
                                                onclick="showImagePreview('../../<?= $today_attendance['bukti_foto'] ?>')">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($rejected_attendance): ?>
                            <!-- Rejected attendance notification -->
                            <div class="p-3 lg:p-4 border border-red-500/30 rounded-lg bg-red-500/10 mb-4">
                                <div class="flex items-center text-red-500">
                                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                                    <div>
                                        <p class="font-medium">Pengajuan absensi Anda ditolak</p>
                                        <p class="text-xs mt-1">Silakan ajukan ulang absensi Anda dengan informasi yang benar</p>
                                    </div>
                                </div>
                            </div>

                            <!-- IP Restriction Notice -->
                            <div class="mb-4 p-3 rounded-lg border <?= is_ip_allowed_for_attendance() ? 'bg-green-500/10 border-green-500/30' : 'bg-yellow-500/10 border-yellow-500/30' ?>">
                                <div class="flex items-start gap-2">
                                    <i class="fas <?= is_ip_allowed_for_attendance() ? 'fa-check-circle text-green-500' : 'fa-info-circle text-yellow-500' ?> mt-0.5"></i>
                                    <div class="flex-1">
                                        <?php if (is_ip_allowed_for_attendance()): ?>
                                            <p class="text-sm text-green-500 font-medium">Anda terhubung dari lokasi yang diizinkan</p>
                                            <p class="text-xs text-green-400/80 mt-1">IP: <?= get_client_ip() ?></p>
                                        <?php else: ?>
                                            <p class="text-sm text-yellow-500 font-medium">Peringatan: Lokasi Tidak Diizinkan</p>
                                            <p class="text-xs text-yellow-400/80 mt-1">Absensi hanya dapat dilakukan dari lokasi sekolah (IP: 125.163.149.128)</p>
                                            <p class="text-xs text-yellow-400/80">IP Anda saat ini: <?= get_client_ip() ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Resubmission form (simplified - admin determines status) -->
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Keterangan (opsional)</label>
                                    <textarea name="keterangan" rows="3"
                                        class="w-full px-3 lg:px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white
                                        focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400"
                                        placeholder="Masukkan keterangan jika ada..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Bukti (opsional)</label>
                                    <input type="file" name="bukti_foto" accept="image/*"
                                        class="w-full px-3 lg:px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white
                                        focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 text-sm">
                                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, max 2MB.</p>
                                </div>
                                            focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 text-sm">
                                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, max 2MB.</p>

                                        <?php if (!empty($rejected_attendance['bukti_foto'])): ?>
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="text-sm text-gray-400">Bukti sebelumnya:</span>
                                                <a href="#" onclick="showImagePreview('../../<?= $rejected_attendance['bukti_foto'] ?>')" class="text-teal-300 hover:text-purple-300 text-sm flex items-center">
                                                    <i class="fas fa-image mr-1"></i> Lihat
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Hidden input for network info -->
                                <input type="hidden" name="network_info" id="network_info">
                                
                                <!-- Hidden input for location data -->
                                <input type="hidden" name="location_data" id="location-data">

                                <div class="pt-2">
                                    <button type="submit" name="submit_attendance"
                                        <?php if (!is_ip_allowed_for_attendance()): ?>disabled<?php endif; ?>
                                        class="px-4 lg:px-6 py-2 lg:py-3 <?= is_ip_allowed_for_attendance() ? 'bg-teal-500 hover:bg-teal-600' : 'bg-gray-600 cursor-not-allowed' ?> rounded-lg font-medium 
                                        text-white transition-colors flex items-center justify-center w-full sm:w-auto text-sm">
                                        <i class="fas fa-paper-plane mr-2"></i> 
                                        <?= is_ip_allowed_for_attendance() ? 'Kirim Ulang Absensi' : 'Tidak Dapat Mengirim (IP Tidak Diizinkan)' ?>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- IP Restriction Notice -->
                            <div class="mb-4 p-3 rounded-lg border <?= is_ip_allowed_for_attendance() ? 'bg-green-500/10 border-green-500/30' : 'bg-yellow-500/10 border-yellow-500/30' ?>">
                                <div class="flex items-start gap-2">
                                    <i class="fas <?= is_ip_allowed_for_attendance() ? 'fa-check-circle text-green-500' : 'fa-info-circle text-yellow-500' ?> mt-0.5"></i>
                                    <div class="flex-1">
                                        <?php if (is_ip_allowed_for_attendance()): ?>
                                            <p class="text-sm text-green-500 font-medium">Anda terhubung dari lokasi yang diizinkan</p>
                                            <p class="text-xs text-green-400/80 mt-1">IP: <?= get_client_ip() ?></p>
                                        <?php else: ?>
                                            <p class="text-sm text-yellow-500 font-medium">Peringatan: Lokasi Tidak Diizinkan</p>
                                            <p class="text-xs text-yellow-400/80 mt-1">Absensi hanya dapat dilakukan dari lokasi sekolah (IP: 125.163.149.128)</p>
                                            <p class="text-xs text-yellow-400/80">IP Anda saat ini: <?= get_client_ip() ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Attendance submission form -->
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Keterangan (opsional)</label>
                                    <textarea name="keterangan" rows="3"
                                        class="w-full px-3 lg:px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white
                                        focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400"
                                        placeholder="Masukkan keterangan jika ada..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Metode Bukti Absensi</label>
                                    <select id="bukti-method" class="w-full px-3 lg:px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 text-sm mb-4">
                                        <option value="upload">Unggah File Gambar</option>
                                        <option value="camera">Ambil Foto dari Kamera</option>
                                    </select>
                                </div>

                                <div id="upload-section">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Unggah Bukti (wajib)</label>
                                    <input type="file" name="bukti_foto" id="bukti-file-input" accept="image/*"
                                        class="w-full px-3 lg:px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white
                                        focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 text-sm">
                                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, max 2MB.</p>
                                </div>

                                <!-- Camera Section -->
                                <div id="camera-section" class="space-y-4 hidden">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Ambil Foto (wajib untuk absensi)</label>
                                        <div class="relative">
                                            <video id="camera-preview" autoplay playsinline muted
                                                class="w-full rounded-lg bg-gray-800 border border-gray-700"
                                                style="max-height: 300px; object-fit: cover;"></video>
                                            <canvas id="camera-canvas" class="hidden"></canvas>
                                            <img id="camera-result" class="hidden w-full rounded-lg border border-gray-700" alt="Captured photo">
                                            <div id="camera-overlay" class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-lg">
                                                <div class="text-center text-white">
                                                    <i class="fas fa-camera text-3xl mb-2"></i>
                                                    <p class="text-sm">Klik tombol "Ambil Foto" untuk memulai kamera</p>
                                                </div>
                                            </div>
                                            <div id="camera-loading" class="absolute inset-0 bg-gray-900/80 flex items-center justify-center rounded-lg hidden">
                                                <div class="text-center text-white">
                                                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                                    <p class="text-sm">Memuat kamera...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 flex-wrap">
                                        <button type="button" id="start-camera-btn"
                                            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium text-white transition-colors text-sm flex items-center">
                                            <i class="fas fa-camera mr-2"></i> Ambil Foto
                                        </button>
                                        <button type="button" id="capture-btn"
                                            class="px-3 py-2 bg-green-600 hover:bg-green-700 rounded-lg font-medium text-white transition-colors text-sm flex items-center hidden">
                                            <i class="fas fa-circle mr-2"></i> Tangkap
                                        </button>
                                        <button type="button" id="retake-btn"
                                            class="px-3 py-2 bg-yellow-600 hover:bg-yellow-700 rounded-lg font-medium text-white transition-colors text-sm flex items-center hidden">
                                            <i class="fas fa-redo mr-2"></i> Ambil Ulang
                                        </button>
                                        <button type="button" id="switch-camera-btn"
                                            class="px-3 py-2 bg-teal-500 hover:bg-teal-600 rounded-lg font-medium text-white transition-colors text-sm flex items-center">
                                            <i class="fas fa-sync-alt mr-2"></i> Ganti Kamera
                                        </button>
                                    </div>
                                </div>

                                <!-- Hidden inputs for camera data -->
                                <input type="hidden" name="camera_image_data" id="camera-image-data">

                                <!-- Hidden input for network info -->
                                <input type="hidden" name="network_info" id="network_info">
                                
                                <!-- Hidden input for location data -->
                                <input type="hidden" name="location_data" id="location-data">

                                <div>
                                    <button type="submit" name="submit_attendance"
                                        <?php if (!is_ip_allowed_for_attendance()): ?>disabled<?php endif; ?>
                                        class="px-4 lg:px-6 py-2 lg:py-3 <?= is_ip_allowed_for_attendance() ? 'bg-teal-500 hover:bg-teal-600' : 'bg-gray-600 cursor-not-allowed' ?> rounded-lg font-medium 
                                        text-white transition-colors flex items-center justify-center w-full sm:w-auto text-sm">
                                        <i class="fas fa-paper-plane mr-2"></i> 
                                        <?= is_ip_allowed_for_attendance() ? 'Kirim Absensi' : 'Tidak Dapat Mengirim (IP Tidak Diizinkan)' ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Attendance Summary Stats -->
                    <div class="glass-effect rounded-xl p-4 lg:p-6">
                        <h3 class="font-semibold text-base lg:text-lg mb-4 flex items-center">
                            <i class="fas fa-chart-pie text-teal-400 mr-2"></i>
                            Ringkasan Kehadiran
                        </h3>

                        <div class="mb-6">
                            <!-- Attendance Percentage -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-400">Persentase Kehadiran</span>
                                <span class="text-sm font-medium"><?= $attendance_percentage ?>%</span>
                            </div>
                            <div class="w-full bg-gray-800/60 rounded-full h-2">
                                <div class="bg-gradient-to-r from-teal-400 to-indigo-500 h-2 rounded-full"
                                    style="width: <?= $attendance_percentage ?>%"></div>
                            </div>
                        </div>

                        <!-- Chart -->
                        <div class="aspect-square mb-4">
                            <canvas id="attendanceChart"></canvas>
                        </div>

                        <!-- Stats Legend -->
                        <div class="grid grid-cols-2 gap-3 text-xs lg:text-sm">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                    <span>Hadir</span>
                                </div>
                                <span class="font-medium"><?= $attendance_summary['hadir'] ?></span>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                                    <span>Sakit</span>
                                </div>
                                <span class="font-medium"><?= $attendance_summary['sakit'] ?></span>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-teal-400 mr-2"></span>
                                    <span>Izin</span>
                                </div>
                                <span class="font-medium"><?= $attendance_summary['izin'] ?></span>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-orange-500 mr-2"></span>
                                    <span>Terlambat</span>
                                </div>
                                <span class="font-medium"><?= $attendance_summary['terlambat'] ?></span>
                            </div>

                            <div class="flex justify-between items-center col-span-2">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                    <span>Alpha</span>
                                </div>
                                <span class="font-medium"><?= $attendance_summary['alpha'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests and Calendar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
                    <!-- Pending Approval Requests -->
                    <div class="glass-effect rounded-xl overflow-hidden">
                        <div class="bg-gradient-to-r from-teal-500/30 to-indigo-900/40 p-4 border-b border-gray-800">
                            <h3 class="font-semibold flex items-center text-base">
                                <i class="fas fa-clock text-teal-400 mr-2"></i>
                                Permintaan Menunggu Persetujuan
                            </h3>
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Sakit, Izin, dan Terlambat memerlukan persetujuan admin. Hadir disetujui otomatis.
                            </p>
                        </div>

                        <?php if (count($pending_requests) > 0): ?>
                            <div class="divide-y divide-gray-800 max-h-[400px] overflow-y-auto">
                                <?php foreach ($pending_requests as $request): ?>
                                    <div class="p-3 lg:p-4 hover:bg-gray-800/30 transition-colors">
                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                            <div>
                                                <!-- Request Type Badge -->
                                                <span class="px-3 py-1 rounded-full text-xs font-medium inline-flex items-center
                                                <?php
                                                switch ($request['status']) {
                                                    case 'Sakit':
                                                        echo 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/30';
                                                        break;
                                                    case 'Izin':
                                                        echo 'bg-teal-500/10 text-teal-400 border border-teal-500/30';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-500/10 text-gray-500 border border-gray-500/30';
                                                }
                                                ?>">
                                                    <i class="fas <?= $request['status'] === 'Sakit' ? 'fa-hospital' : 'fa-clipboard-list' ?> mr-1"></i>
                                                    <?= $request['status'] ?>
                                                </span>

                                                <!-- Date and Time -->
                                                <div class="mt-2 text-xs text-gray-400">
                                                    <span class="inline-block mr-3">
                                                        <i class="far fa-calendar-alt mr-1"></i>
                                                        <?= date('d M Y', strtotime($request['tanggal'])) ?>
                                                    </span>
                                                    <?php if ($request['created_at']): ?>
                                                        <span class="inline-block">
                                                            <i class="far fa-clock mr-1"></i>
                                                            <?= date('H:i', strtotime($request['created_at'])) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Display thumbnail if evidence exists -->
                                            <?php if ($request['bukti_foto']): ?>
                                                <div class="shrink-0">
                                                    <img src="../../<?= $request['bukti_foto'] ?>"
                                                        alt="Bukti"
                                                        class="w-12 h-12 object-cover rounded-md border border-gray-700"
                                                        onclick="showImagePreview('../../<?= $request['bukti_foto'] ?>')">
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Reason if any -->
                                        <?php if ($request['keterangan']): ?>
                                            <div class="mt-2 text-sm text-gray-300">
                                                <div class="text-xs text-gray-400 mb-1">Keterangan:</div>
                                                <p class="text-xs"><?= htmlspecialchars($request['keterangan']) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Pending Badge -->
                                        <div class="mt-3 flex items-center justify-between flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-500 border border-yellow-500/30">
                                                <i class="fas fa-hourglass-half mr-1.5"></i>
                                                Menunggu Persetujuan
                                            </span>

                                            <!-- Cancel submission -->
                                            <button type="button" onclick="showConfirmationModal(<?= $request['id'] ?>)" class="text-red-400 hover:text-red-300 transition-colors text-sm flex items-center">
                                                <i class="fas fa-trash-alt mr-1"></i>
                                                Batalkan
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-800/50 mb-4">
                                    <i class="fas fa-check-circle text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-400">Tidak ada permintaan yang tertunda.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Attendance Calendar -->
                    <div class="glass-effect rounded-xl overflow-hidden">
                        <div class="bg-gradient-to-r from-teal-500/30 to-indigo-900/40 p-4 border-b border-gray-800">
                            <h3 class="font-semibold flex items-center text-base">
                                <i class="fas fa-calendar-alt text-teal-400 mr-2"></i>
                                Kehadiran Bulan Ini
                            </h3>
                        </div>

                        <div class="p-4">
                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                                <div class="text-xs font-medium text-gray-400">Min</div>
                                <div class="text-xs font-medium text-gray-400">Sen</div>
                                <div class="text-xs font-medium text-gray-400">Sel</div>
                                <div class="text-xs font-medium text-gray-400">Rab</div>
                                <div class="text-xs font-medium text-gray-400">Kam</div>
                                <div class="text-xs font-medium text-gray-400">Jum</div>
                                <div class="text-xs font-medium text-gray-400">Sab</div>
                            </div>

                            <div id="calendarGrid" class="grid grid-cols-7 gap-1">
                                <!-- Calendar will be generated by JavaScript -->
                            </div>

                            <!-- Calendar Legend -->
                            <div class="p-4 border-t border-gray-800 grid grid-cols-3 gap-2">
                                <div class="flex items-center">
                                    <span class="h-3 w-3 rounded-full bg-green-500 mr-2"></span>
                                    <span class="text-xs">Hadir</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="h-3 w-3 rounded-full bg-yellow-500 mr-2"></span>
                                    <span class="text-xs">Sakit</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="h-3 w-3 rounded-full bg-teal-400 mr-2"></span>
                                    <span class="text-xs">Izin</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="h-3 w-3 rounded-full bg-orange-500 mr-2"></span>
                                    <span class="text-xs">Terlambat</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="h-3 w-3 rounded-full bg-red-500 mr-2"></span>
                                    <span class="text-xs">Alpha</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="h-3 w-3 rounded-full bg-gray-600 mr-2"></span>
                                    <span class="text-xs">Belum Absen</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Image Preview Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center p-4">
        <div class="relative max-w-xl w-full">
            <button onclick="closeImagePreview()" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                <i class="fas fa-times text-xl"></i>
            </button>
            <img id="previewImage" src="" alt="Preview" class="w-full rounded-lg">
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="glass-effect rounded-xl max-w-md w-full p-6 border border-teal-500/30">
            <h3 class="text-lg font-semibold mb-4 text-white">Konfirmasi</h3>
            <p class="text-gray-300 mb-6">Apakah Anda yakin ingin membatalkan pengajuan absensi ini?</p>
            <form method="POST" id="cancelForm">
                <input type="hidden" name="request_id" id="requestIdInput">
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeConfirmationModal()"
                        class="px-4 py-2 bg-gray-600/50 hover:bg-gray-600 rounded-lg text-white transition-colors">
                        <i class="fas fa-times mr-2"></i> Batal
                    </button>
                    <button type="submit" name="cancel_request"
                        class="px-4 py-2 bg-red-500/80 hover:bg-red-600 rounded-lg text-white transition-colors">
                        <i class="fas fa-trash-alt mr-2"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1) ?>assets/js/diome.js"></script>
    <script>
        // Camera variables
        let stream = null;
        let facingMode = "user"; // "user" for front camera, "environment" for back camera

        // Camera DOM elements
        const cameraPreview = document.getElementById('camera-preview');
        const cameraCanvas = document.getElementById('camera-canvas');
        const cameraResult = document.getElementById('camera-result');
        const cameraOverlay = document.getElementById('camera-overlay');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');
        const switchCameraBtn = document.getElementById('switch-camera-btn');
        const cameraImageData = document.getElementById('camera-image-data');

        document.addEventListener('DOMContentLoaded', function() {
            // Update current time every second
            function updateTime() {
                const timeElement = document.getElementById('current-time');
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                timeElement.textContent = timeString + ' WIB';
            }
            setInterval(updateTime, 1000);
            updateTime(); // Initial call

            // Show/hide additional fields based on attendance status
            document.querySelectorAll('input[name="status"]').forEach(input => {
                input.addEventListener('change', function() {
                    const additionalFields = document.getElementById('additionalFields');
                    if (this.value === 'Hadir') {
                        additionalFields.classList.add('hidden');
                    } else {
                        additionalFields.classList.remove('hidden');
                    }
                });
            });

            // Image preview functionality
            function showImagePreview(src) {
                const modal = document.getElementById('imageModal');
                const previewImage = document.getElementById('previewImage');
                previewImage.src = src;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeImagePreview() {
                const modal = document.getElementById('imageModal');
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Close modal when clicking outside the image
            document.getElementById('imageModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeImagePreview();
                }
            });

            // Initialize attendance chart
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($chart_data['labels']) ?>,
                    datasets: [{
                        data: <?= json_encode($chart_data['data']) ?>,
                        backgroundColor: <?= json_encode($chart_data['colors']) ?>,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            borderColor: 'rgba(20, 184, 166, 0.3)',
                            borderWidth: 1,
                            displayColors: true,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Generate calendar
            function generateCalendar() {
                const date = new Date();
                const year = date.getFullYear();
                const month = date.getMonth();

                const attendanceData = <?= json_encode($attendance_history) ?>;

                // Map attendance data by day
                const attendanceMap = {};
                attendanceData.forEach(record => {
                    const day = new Date(record.tanggal).getDate();
                    attendanceMap[day] = record.status;
                });

                // Get first day of month
                const firstDay = new Date(year, month, 1).getDay();
                // Get number of days in month
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                // Get calendar grid
                const calendarGrid = document.getElementById('calendarGrid');
                calendarGrid.innerHTML = '';

                // Add empty cells for days before the first day of month
                for (let i = 0; i < firstDay; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'h-10 rounded-md';
                    calendarGrid.appendChild(emptyCell);
                }

                // Add cells for each day
                for (let day = 1; day <= daysInMonth; day++) {
                    const cell = document.createElement('div');

                    // Check if date is in the future
                    const currentDate = new Date(year, month, day);
                    const isToday = currentDate.toDateString() === new Date().toDateString();
                    const isPast = currentDate < new Date().setHours(0, 0, 0, 0);

                    // Basic cell styling
                    cell.className = 'h-10 flex flex-col items-center justify-center rounded-md relative';

                    // Day number
                    const dayNumber = document.createElement('span');
                    dayNumber.className = 'text-xs ' + (isToday ? 'font-bold' : '');
                    dayNumber.textContent = day;
                    cell.appendChild(dayNumber);

                    // Status dot
                    if (attendanceMap[day]) {
                        const statusDot = document.createElement('span');
                        switch (attendanceMap[day]) {
                            case 'Hadir':
                                statusDot.className = 'h-2 w-2 rounded-full bg-green-500 mt-1';
                                cell.title = 'Hadir';
                                break;
                            case 'Sakit':
                                statusDot.className = 'h-2 w-2 rounded-full bg-yellow-500 mt-1';
                                cell.title = 'Sakit';
                                break;
                            case 'Izin':
                                statusDot.className = 'h-2 w-2 rounded-full bg-teal-400 mt-1';
                                cell.title = 'Izin';
                                break;
                            case 'Terlambat':
                                statusDot.className = 'h-2 w-2 rounded-full bg-orange-500 mt-1';
                                cell.title = 'Terlambat';
                                break;
                            case 'Alpha':
                                statusDot.className = 'h-2 w-2 rounded-full bg-red-500 mt-1';
                                cell.title = 'Alpha';
                                break;
                            default:
                                statusDot.className = 'h-2 w-2 rounded-full bg-gray-600 mt-1';
                                cell.title = 'Tidak hadir';
                        }
                        cell.appendChild(statusDot);
                    } else if (isPast && !attendanceMap[day]) {
                        cell.classList.add('bg-gray-800/30');
                        const statusDot = document.createElement('span');
                        statusDot.className = 'h-2 w-2 rounded-full bg-gray-600 mt-1';
                        cell.title = 'Tidak hadir';
                        cell.appendChild(statusDot);
                    }

                    // Style today
                    if (isToday) {
                        cell.classList.add('ring-2', 'ring-teal-400');
                        dayNumber.classList.add('text-teal-300');
                    }

                    // Add hover effect
                    cell.classList.add('hover:bg-gray-800/50', 'transition-colors');

                    calendarGrid.appendChild(cell);
                }
            }

            generateCalendar();
        });

        // Add these functions to handle the modal
        function showConfirmationModal(requestId) {
            const modal = document.getElementById('confirmationModal');
            const requestIdInput = document.getElementById('requestIdInput');
            requestIdInput.value = requestId;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeConfirmationModal() {
            const modal = document.getElementById('confirmationModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('confirmationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmationModal();
            }
        });

        // Add this to your existing script section
        // Get network information
        function getNetworkInfo() {
            const networkInfo = document.getElementById('network_info');
            let info = {};

            // Get connection information if available
            if ('connection' in navigator) {
                const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                if (connection) {
                    info = {
                        type: connection.type || 'unknown',
                        effectiveType: connection.effectiveType || 'unknown',
                        downlink: connection.downlink || 'unknown',
                        rtt: connection.rtt || 'unknown'
                    };
                }
            }

            // Get user agent
            info.userAgent = navigator.userAgent;

            // Get platform
            info.platform = navigator.platform;

            // Store as JSON string
            if (networkInfo) {
                networkInfo.value = JSON.stringify(info);
            }
        }

        // Initialize network info on page load
        getNetworkInfo();

        // Start camera function
        function startCamera() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                // Stop any existing stream
                if (stream) {
                    stopCamera();
                }

                // Show loading indicator
                const loadingIndicator = document.getElementById('camera-loading');
                if (loadingIndicator) loadingIndicator.classList.remove('hidden');

                // Reset camera UI
                cameraOverlay.classList.add('hidden');
                captureBtn.classList.remove('hidden');
                retakeBtn.classList.add('hidden');

                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: facingMode,
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            }
                        },
                        audio: false
                    })
                    .then(function(mediaStream) {
                        stream = mediaStream;
                        cameraPreview.srcObject = stream;
                        cameraPreview.play().then(() => {
                            // Hide loading indicator when camera starts
                            if (loadingIndicator) loadingIndicator.classList.add('hidden');
                        });
                    })
                    .catch(function(error) {
                        console.error('Camera error:', error);
                        if (loadingIndicator) loadingIndicator.classList.add('hidden');

                        // Show user-friendly error message
                        let errorMessage = 'Tidak dapat mengakses kamera. ';

                        if (error.name === 'NotAllowedError') {
                            errorMessage += 'Harap berikan izin kamera di browser Anda.';
                        } else if (error.name === 'NotFoundError') {
                            errorMessage += 'Kamera tidak ditemukan di perangkat Anda.';
                        } else if (error.name === 'NotReadableError') {
                            errorMessage += 'Kamera sedang digunakan aplikasi lain.';
                        } else {
                            errorMessage += 'Terjadi kesalahan teknis.';
                        }

                        alert(errorMessage);
                    });
            } else {
                alert('Browser anda tidak mendukung kamera. Gunakan browser terbaru seperti Chrome atau Firefox.');
            }
        }

        // Stop camera function
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => {
                    track.stop();
                });
                stream = null;
            }
        }

        // Capture photo
        captureBtn.addEventListener('click', function() {
            if (stream) {
                // Set canvas dimensions to match video
                cameraCanvas.width = cameraPreview.videoWidth;
                cameraCanvas.height = cameraPreview.videoHeight;

                // Draw video frame to canvas
                const context = cameraCanvas.getContext('2d');
                context.drawImage(cameraPreview, 0, 0, cameraCanvas.width, cameraCanvas.height);

                // Get image data and show preview
                const imageData = cameraCanvas.toDataURL('image/jpeg');
                cameraResult.src = imageData;
                cameraImageData.value = imageData; // Store in hidden input

                // Show captured image and retake button
                cameraOverlay.classList.remove('hidden');
                captureBtn.classList.add('hidden');
                retakeBtn.classList.remove('hidden');
            }
        });

        // Retake photo
        retakeBtn.addEventListener('click', function() {
            cameraOverlay.classList.add('hidden');
            captureBtn.classList.remove('hidden');
            retakeBtn.classList.add('hidden');
            cameraImageData.value = '';
        });

        // Switch camera
        switchCameraBtn.addEventListener('click', function() {
            facingMode = facingMode === "user" ? "environment" : "user";
            startCamera();
        });

        // Start camera button
        const startCameraBtn = document.getElementById('start-camera-btn');
        if (startCameraBtn) {
            startCameraBtn.addEventListener('click', function() {
                startCamera();
            });
        }

        // Form submission validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const methodSelect = document.getElementById('bukti-method');
                if (methodSelect && methodSelect.value === 'camera') {
                    const cameraImageData = document.getElementById('camera-image-data');
                    if (!cameraImageData || !cameraImageData.value) {
                        e.preventDefault();
                        alert('Anda harus mengambil foto untuk absensi melalui kamera.');
                        return;
                    }
                }
            });
        }

        // Stop camera when page is unloaded
        window.addEventListener('beforeunload', stopCamera);

        // Add sidebar toggle function (mobile responsive)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');

            if (sidebar.classList.contains('-translate-x-full')) {
                // Open sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                // Close sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Update time for mobile view too
        function updateMobileTime() {
            const mobileTimeElement = document.getElementById('current-time-mobile');
            if (mobileTimeElement) {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
                mobileTimeElement.textContent = timeString;
            }
        }

        // Add mobile time updater
        setInterval(updateMobileTime, 60000); // Update every minute
        updateMobileTime(); // Initial call

        // Make sure modals close when pressing escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImagePreview();
                closeConfirmationModal();

                // Also close sidebar on mobile
                if (window.innerWidth < 1024) { // lg breakpoint in Tailwind
                    const sidebar = document.getElementById('sidebar');
                    if (!sidebar.classList.contains('-translate-x-full')) {
                        toggleSidebar();
                    }
                }
            }
        });

        // Adjust camera UI for smaller screens
        function adjustCameraUI() {
            const cameraPreview = document.getElementById('camera-preview');
            if (cameraPreview) {
                // Set height based on screen size
                if (window.innerWidth < 768) { // md breakpoint
                    cameraPreview.style.maxHeight = '200px';
                } else {
                    cameraPreview.style.maxHeight = '300px';
                }
            }
        }

        // Run on page load and window resize
        window.addEventListener('resize', adjustCameraUI);
        window.addEventListener('DOMContentLoaded', function() {
            adjustCameraUI();
        });

        // Update the calendar based on screen size
        function adjustCalendar() {
            // Regenerate the calendar to adjust sizes
            generateCalendar();

            // Make day indicators smaller on mobile
            if (window.innerWidth < 640) { // sm breakpoint
                document.querySelectorAll('#calendarGrid > div').forEach(cell => {
                    const dayNumber = cell.querySelector('span:first-child');
                    if (dayNumber) {
                        dayNumber.classList.add('text-[10px]');
                    }
                });
            }
        }

        // Add resize listener for calendar adjustments
        window.addEventListener('resize', adjustCalendar);

        // Image preview functionality 
        function showImagePreview(src) {
            const modal = document.getElementById('imageModal');
            const previewImage = document.getElementById('previewImage');
            previewImage.src = src;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImagePreview() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImagePreview();
            }
        });

        // Toggle bukti method
        document.addEventListener('DOMContentLoaded', function() {
            const methodSelect = document.getElementById('bukti-method');
            if(methodSelect) {
                methodSelect.addEventListener('change', function() {
                    const uploadSection = document.getElementById('upload-section');
                    const cameraSection = document.getElementById('camera-section');
                    const fileInput = document.getElementById('bukti-file-input');
                    const cameraInput = document.getElementById('camera-image-data');
                    
                    if(this.value === 'upload') {
                        uploadSection.classList.remove('hidden');
                        cameraSection.classList.add('hidden');
                        cameraInput.value = ''; // clear camera data if switching
                        if(fileInput) fileInput.required = true;
                    } else {
                        uploadSection.classList.add('hidden');
                        cameraSection.classList.remove('hidden');
                        if(fileInput) { fileInput.value = ''; fileInput.required = false; }
                    }
                });

                // Set initial required state
                const fileInput = document.getElementById('bukti-file-input');
                if (fileInput && methodSelect.value === 'upload') {
                    fileInput.required = true;
                }
            }
        });
    </script>
    <!-- Theme Toggle Inline Handler -->
    <script>
        window.toggleTheme = function() {
            const html = document.documentElement;
            const isLight = html.classList.toggle('light-theme');
            const theme = isLight ? 'light' : 'dark';
            localStorage.setItem('app-theme', theme);
            const btn = document.getElementById('theme-toggle-btn');
            if (btn) {
                const icon = btn.querySelector('i');
                const text = btn.querySelector('span');
                if (isLight) {
                    if (icon) { icon.className = 'fas fa-moon'; icon.style.color = '#6366f1'; }
                    if (text) text.textContent = 'Mode Gelap';
                } else {
                    if (icon) { icon.className = 'fas fa-sun'; icon.style.color = '#fbbf24'; }
                    if (text) text.textContent = 'Mode Terang';
                }
            }
        };
    </script>

    <!-- Geolocation Script -->
    <script>
        // Geolocation variables
        let userLocation = null;
        let locationPermissionGranted = false;

        // Request location permission on page load
        window.addEventListener('DOMContentLoaded', function() {
            requestLocationPermission();
        });

        function requestLocationPermission() {
            if (!navigator.geolocation) {
                console.warn('Geolocation tidak didukung oleh browser ini');
                showLocationNotification('Browser Anda tidak mendukung deteksi lokasi', 'warning');
                return;
            }

            // Show loading notification
            showLocationNotification('Meminta izin lokasi...', 'info');

            // Request location with high accuracy
            navigator.geolocation.getCurrentPosition(
                // Success callback
                function(position) {
                    userLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: new Date().toISOString()
                    };
                    locationPermissionGranted = true;
                    
                    // Store location in hidden input
                    const locationInput = document.getElementById('location-data');
                    if (locationInput) {
                        locationInput.value = JSON.stringify(userLocation);
                    }

                    console.log('Lokasi berhasil didapat:', userLocation);
                    showLocationNotification('Lokasi berhasil dideteksi', 'success');
                    
                    // Hide notification after 3 seconds
                    setTimeout(hideLocationNotification, 3000);
                },
                // Error callback
                function(error) {
                    let errorMessage = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Izin lokasi ditolak. Beberapa fitur mungkin tidak tersedia.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Permintaan lokasi timeout.';
                            break;
                        default:
                            errorMessage = 'Terjadi kesalahan saat mendapatkan lokasi.';
                    }
                    console.error('Geolocation error:', error);
                    showLocationNotification(errorMessage, 'error');
                    
                    // Hide notification after 5 seconds
                    setTimeout(hideLocationNotification, 5000);
                },
                // Options
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function showLocationNotification(message, type) {
            // Remove existing notification if any
            hideLocationNotification();

            const notification = document.createElement('div');
            notification.id = 'location-notification';
            notification.className = 'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm animate-fade-in-up';
            
            let bgColor, borderColor, iconClass, iconColor;
            switch(type) {
                case 'success':
                    bgColor = 'bg-green-500/10';
                    borderColor = 'border-green-500/30';
                    iconClass = 'fa-check-circle';
                    iconColor = 'text-green-500';
                    break;
                case 'error':
                    bgColor = 'bg-red-500/10';
                    borderColor = 'border-red-500/30';
                    iconClass = 'fa-exclamation-circle';
                    iconColor = 'text-red-500';
                    break;
                case 'warning':
                    bgColor = 'bg-yellow-500/10';
                    borderColor = 'border-yellow-500/30';
                    iconClass = 'fa-exclamation-triangle';
                    iconColor = 'text-yellow-500';
                    break;
                case 'info':
                default:
                    bgColor = 'bg-blue-500/10';
                    borderColor = 'border-blue-500/30';
                    iconClass = 'fa-info-circle';
                    iconColor = 'text-blue-500';
            }

            notification.className += ` ${bgColor} border ${borderColor}`;
            notification.innerHTML = `
                <div class="flex items-start gap-3">
                    <i class="fas ${iconClass} ${iconColor} mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm text-white">${message}</p>
                    </div>
                    <button onclick="hideLocationNotification()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);
        }

        function hideLocationNotification() {
            const notification = document.getElementById('location-notification');
            if (notification) {
                notification.remove();
            }
        }

        // Update location before form submission
        document.addEventListener('submit', function(e) {
            if (e.target.querySelector('[name="submit_attendance"]')) {
                if (locationPermissionGranted && userLocation) {
                    const locationInput = document.getElementById('location-data');
                    if (locationInput) {
                        locationInput.value = JSON.stringify(userLocation);
                    }
                }
            }
        });
    </script>


    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1) ?>assets/js/theme.js"></script>
</body>

</html>