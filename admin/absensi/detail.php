<?php
require_once '../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check for required ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Get detailed attendance information
$sql = "SELECT a.*, s.nama_lengkap, s.nis, s.kelas, s.jurusan, s.foto_profil, s.email
        FROM absensi a
        JOIN siswa s ON a.siswa_id = s.id
        WHERE a.id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);
$absensi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$absensi) {
    header("Location: index.php?error=not_found");
    exit();
}

// Update approval status if requested
if (isset($_POST['approve'])) {
    $approval_status = $_POST['approval_status'];

    try {
        $conn->beginTransaction();

        $sql = "UPDATE absensi SET approval_status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['status' => $approval_status, 'id' => $id]);

        // Log activity
        $action = $approval_status == 'Approved' ? 'menyetujui' : 'menolak';
        $sql = "INSERT INTO activity_log (user_type, user_id, activity_type, description) 
                VALUES ('admin', :admin_id, 'approval', :description)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'admin_id' => $_SESSION['admin_id'],
            'description' => "Admin $action absensi " . $absensi['status'] . " untuk " . $absensi['nama_lengkap']
        ]);

        $conn->commit();

        // Refresh page to show updated information
        header("Location: detail.php?id=$id&updated=true");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}

$status_colors = [
    'Hadir' => 'green',
    'Sakit' => 'yellow',
    'Izin' => 'purple',
    'Terlambat' => 'orange',
    'Alpha' => 'red'
];

$approval_colors = [
    'Pending' => 'yellow',
    'Approved' => 'green',
    'Rejected' => 'red'
];

$status_color = $status_colors[$absensi['status']] ?? 'gray';
$approval_color = $approval_colors[$absensi['approval_status']] ?? 'gray';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Absensi - SMKN 1 Sanden</title>    
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
    <style>
        .glass-effect {
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(20, 184, 166, 0.2);
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.8), 0 2px 4px rgba(0, 0, 0, 0.5);
            border-radius: 4px !important;
        }

        body {
            background: #0f0f13;
            color: #d1d5db;
        }

        .menu-active {
            background: linear-gradient(to right, rgba(20, 184, 166, 0.15), rgba(20, 184, 166, 0.02));
            border-left: 3px solid #14b8a6;
            color: #fff;
            text-shadow: 0 0 8px rgba(20, 184, 166, 0.4);
        }

        /* Mobile responsive styles */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        .mobile-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            transition: opacity 0.3s ease-in-out;
        }

        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Improved mobile image viewer */
        .image-preview {
            transition: all 0.3s ease;
        }

        .image-preview:active {
            transform: scale(0.98);
        }

        /* Fix for mobile browsers */
        @supports (-webkit-touch-callout: none) {
            .min-h-screen {
                min-height: -webkit-fill-available;
            }
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

        <nav class="p-4 space-y-2 overflow-y-auto no-scrollbar" style="max-height: calc(100vh - 76px);">
            <a href="../dashboard/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="index.php" class="flex items-center gap-3 text-white/90 p-3 rounded-lg menu-active">
                <i class="fas fa-calendar-check text-teal-400"></i>
                <span>Absensi</span>
            </a>
            <a href="../siswa/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-users"></i>
                <span>Data Siswa</span>
            </a>
            <a href="../laporan/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <a href="../profil/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-user-cog"></i>
                <span>Profil</span>
            </a>

            <hr class="border-gray-700/50 my-4">

            <!-- Theme Toggle -->
            <button id="theme-toggle-btn" onclick="toggleTheme()" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-400/10 transition-colors w-full text-left">
                <i class="fas fa-sun text-yellow-400"></i>
                <span>Mode Terang</span>
            </button>

            <a href="../logout.php" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-red-500/10 hover:text-red-500 transition-colors mt-10">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen bg-transparent transition-all duration-300">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-gray-900/60 backdrop-blur-lg sticky top-0 z-30 px-4 py-3 flex items-center justify-between border-b border-teal-500/20">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="text-white p-2 -ml-2 rounded-lg hover:bg-gray-800/60" aria-label="Menu">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <img src="../../assets/default/smkn1sanden.png" alt="SMKN 1 Sanden" class="h-8 w-auto">
            </div>
            <div class="flex items-center gap-3">
                <span id="current-time-mobile" class="text-sm font-medium hidden sm:block"></span>
                <?php
                // Use admin photo from session if available
                $photo_path = $_SESSION['admin_photo'] ?? 'assets/default/avatar.png';
                ?>
                <img src="../../<?= $photo_path ?>" alt="Profile" class="h-8 w-8 rounded-full object-cover border border-teal-500/30">
            </div>
        </div>

        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header with back button - enhanced for mobile -->
                <div class="flex items-center mb-6">
                    <a href="index.php" class="mr-3 p-2 rounded-full hover:bg-gray-800/60 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold">Detail Absensi</h1>
                        <p class="text-sm md:text-base text-gray-400">Informasi lengkap kehadiran siswa</p>
                    </div>
                </div>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 mb-6 flex items-center animate-fade-in">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p>Status absensi berhasil diperbarui.</p>
                    </div>
                <?php endif; ?>

                <!-- Student Info Card - Mobile optimized -->
                <div class="glass-effect rounded-xl p-4 md:p-6 mb-6 animate-fade-in">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 md:gap-6">
                        <div class="relative">
                            <img src="../../<?= htmlspecialchars($absensi['foto_profil'] ?: 'assets/default/avatar.png') ?>"
                                alt="<?= htmlspecialchars($absensi['nama_lengkap']) ?>"
                                class="w-20 h-20 md:w-24 md:h-24 rounded-xl object-cover border border-teal-500/30">

                            <!-- Status indicator on the photo for mobile -->
                            <div class="absolute -bottom-2 -right-2 h-7 w-7 rounded-full flex items-center justify-center bg-<?= $status_color ?>-500/20 border border-<?= $status_color ?>-500/40">
                                <?php if ($absensi['status'] == 'Hadir'): ?>
                                    <i class="fas fa-check text-<?= $status_color ?>-500 text-xs"></i>
                                <?php elseif ($absensi['status'] == 'Sakit'): ?>
                                    <i class="fas fa-hospital text-<?= $status_color ?>-500 text-xs"></i>
                                <?php elseif ($absensi['status'] == 'Izin'): ?>
                                    <i class="fas fa-envelope text-<?= $status_color ?>-500 text-xs"></i>
                                <?php elseif ($absensi['status'] == 'Terlambat'): ?>
                                    <i class="fas fa-clock text-<?= $status_color ?>-500 text-xs"></i>
                                <?php elseif ($absensi['status'] == 'Alpha'): ?>
                                    <i class="fas fa-times text-<?= $status_color ?>-500 text-xs"></i>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex-grow text-center sm:text-left">
                            <h3 class="text-lg md:text-xl font-bold"><?= htmlspecialchars($absensi['nama_lengkap']) ?></h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 mt-2 md:mt-3">
                                <div>
                                    <p class="text-gray-400 text-xs">NIS</p>
                                    <p class="text-sm md:text-base"><?= htmlspecialchars($absensi['nis']) ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs">Kelas</p>
                                    <p class="text-sm md:text-base"><?= htmlspecialchars($absensi['kelas']) ?> <?= htmlspecialchars($absensi['jurusan']) ?></p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-gray-400 text-xs">Email</p>
                                    <p class="text-sm md:text-base truncate"><?= htmlspecialchars($absensi['email']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Details - Better spacing for mobile -->
                <div class="glass-effect rounded-xl p-4 md:p-6 mb-4 md:mb-6 animate-fade-in">
                    <h3 class="font-semibold mb-4 text-base md:text-lg">Informasi Absensi</h3>

                    <!-- Status indicators - Mobile optimized -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm bg-<?= $status_color ?>-500/10 text-<?= $status_color ?>-500 border border-<?= $status_color ?>-500/30">
                            <?php if ($absensi['status'] == 'Hadir'): ?>
                                <i class="fas fa-check mr-2"></i>
                            <?php elseif ($absensi['status'] == 'Sakit'): ?>
                                <i class="fas fa-hospital mr-2"></i>
                            <?php elseif ($absensi['status'] == 'Izin'): ?>
                                <i class="fas fa-envelope mr-2"></i>
                            <?php elseif ($absensi['status'] == 'Terlambat'): ?>
                                <i class="fas fa-clock mr-2"></i>
                            <?php elseif ($absensi['status'] == 'Alpha'): ?>
                                <i class="fas fa-times mr-2"></i>
                            <?php endif; ?>
                            <?= $absensi['status'] ?>
                        </span>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm bg-<?= $approval_color ?>-500/10 text-<?= $approval_color ?>-500 border border-<?= $approval_color ?>-500/30">
                            <?php if ($absensi['approval_status'] == 'Approved'): ?>
                                <i class="fas fa-check-circle mr-2"></i>
                            <?php elseif ($absensi['approval_status'] == 'Rejected'): ?>
                                <i class="fas fa-times-circle mr-2"></i>
                            <?php else: ?>
                                <i class="fas fa-clock mr-2"></i>
                            <?php endif; ?>
                            <?= $absensi['approval_status'] ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                        <div>
                            <p class="text-gray-400 text-xs mb-1">Tanggal</p>
                            <p class="text-sm md:text-base font-medium"><?= $absensi['tanggal'] ? date('d F Y', strtotime($absensi['tanggal'])) : '-' ?></p>
                        </div>

                        <div>
                            <p class="text-gray-400 text-xs mb-1">Jam Masuk</p>
                            <p class="text-sm md:text-base font-medium">
                                <?= $absensi['jam_masuk'] && $absensi['jam_masuk'] !== '00:00:00' ? date('H:i', strtotime($absensi['jam_masuk'])) : '-' ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-400 text-xs mb-1">Dicatat Pada</p>
                            <p class="text-sm md:text-base font-medium">
                                <?= $absensi['created_at'] ? date('d/m/Y H:i', strtotime($absensi['created_at'])) : '-' ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 md:mt-6">
                        <p class="text-gray-400 text-xs mb-1">Keterangan</p>
                        <div class="bg-gray-800/50 rounded-lg p-3 min-h-[60px]">
                            <p class="text-sm">
                                <?= $absensi['keterangan'] ? nl2br(htmlspecialchars($absensi['keterangan'])) : '<span class="text-gray-500 italic">Tidak ada keterangan</span>' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Proof Images/Files - Mobile friendly -->
                <div class="glass-effect rounded-xl p-4 md:p-6 mb-4 md:mb-6 animate-fade-in">
                    <h3 class="font-semibold mb-4 text-base md:text-lg">Bukti</h3>

                    <?php if ($absensi['bukti_foto'] || $absensi['bukti_file']): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php if ($absensi['bukti_foto']): ?>
                                <div>
                                    <p class="text-gray-400 text-xs mb-2">Foto Bukti</p>
                                    <a href="../../<?= htmlspecialchars($absensi['bukti_foto']) ?>"
                                        target="_blank"
                                        class="block rounded-lg overflow-hidden image-preview">
                                        <img src="../../<?= htmlspecialchars($absensi['bukti_foto']) ?>"
                                            alt="Bukti Absensi"
                                            class="w-full h-40 md:h-48 object-cover rounded-lg hover:opacity-90 transition-opacity">
                                        <div class="mt-2 flex justify-center items-center text-xs text-teal-300">
                                            <i class="fas fa-search-plus mr-1"></i> Tap untuk memperbesar
                                        </div>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($absensi['bukti_file']): ?>
                                <div>
                                    <p class="text-gray-400 text-xs mb-2">File Bukti</p>
                                    <a href="../../<?= htmlspecialchars($absensi['bukti_file']) ?>"
                                        target="_blank"
                                        class="flex items-center p-4 rounded-lg bg-gray-800/50 hover:bg-gray-800 transition-colors">
                                        <i class="fas fa-file-alt text-teal-400 text-2xl mr-3"></i>
                                        <div class="overflow-hidden">
                                            <p class="truncate font-medium text-sm">Dokumen Pendukung</p>
                                            <p class="text-xs text-gray-400">Download file</p>
                                        </div>
                                        <i class="fas fa-download ml-auto text-gray-400"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-gray-500">
                            <i class="fas fa-folder-open text-3xl mb-2"></i>
                            <p>Tidak ada bukti yang diunggah</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- SECURITY: Complete Security Information -->
                <div class="glass-effect rounded-xl p-4 lg:p-6 mb-6 animate-fade-in">
                    <h3 class="font-semibold text-base lg:text-lg mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-teal-400 mr-2"></i>
                        Informasi Keamanan
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- IP Address -->
                        <div class="bg-gray-800/50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-network-wired mr-1"></i>IP Address</p>
                            <p class="text-sm font-mono text-blue-400">
                                <?= $absensi['ip_address'] ? htmlspecialchars($absensi['ip_address']) : '<span class="text-gray-500">-</span>' ?>
                            </p>
                        </div>

                        <!-- Device Type -->
                        <div class="bg-gray-800/50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-mobile-alt mr-1"></i>Device Type</p>
                            <p class="text-sm">
                                <?= isset($absensi['device_type']) && $absensi['device_type'] ? htmlspecialchars($absensi['device_type']) : '<span class="text-gray-500">-</span>' ?>
                            </p>
                        </div>

                        <!-- Network Type -->
                        <div class="bg-gray-800/50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-wifi mr-1"></i>Network Type</p>
                            <p class="text-sm">
                                <?= isset($absensi['network_type']) && $absensi['network_type'] ? htmlspecialchars($absensi['network_type']) : '<span class="text-gray-500">-</span>' ?>
                            </p>
                        </div>

                        <!-- Location Accuracy -->
                        <div class="bg-gray-800/50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-crosshairs mr-1"></i>GPS Accuracy</p>
                            <p class="text-sm">
                                <?= isset($absensi['location_accuracy']) && $absensi['location_accuracy'] ? number_format($absensi['location_accuracy'], 2) . ' meter' : '<span class="text-gray-500">-</span>' ?>
                            </p>
                        </div>
                    </div>

                    <!-- GPS Coordinates -->
                    <?php if (isset($absensi['latitude']) && isset($absensi['longitude']) && $absensi['latitude'] && $absensi['longitude']): ?>
                        <div class="bg-gray-800/50 rounded-lg p-4 mt-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-map-marker-alt mr-1"></i>GPS Coordinates</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <span class="text-xs text-gray-500">Latitude:</span>
                                    <p class="text-sm font-mono text-green-400"><?= number_format($absensi['latitude'], 6) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Longitude:</span>
                                    <p class="text-sm font-mono text-green-400"><?= number_format($absensi['longitude'], 6) ?></p>
                                </div>
                            </div>
                            <a href="https://www.google.com/maps?q=<?= $absensi['latitude'] ?>,<?= $absensi['longitude'] ?>" 
                               target="_blank"
                               class="inline-flex items-center gap-2 mt-3 px-3 py-1.5 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 rounded-lg text-xs transition-colors">
                                <i class="fas fa-external-link-alt"></i>
                                Lihat di Google Maps
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Security Flags -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Mock GPS Detection -->
                        <div class="bg-gray-800/50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-satellite-dish mr-1"></i>Mock GPS Detection</p>
                            <?php if (isset($absensi['mock_detected']) && $absensi['mock_detected']): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-yellow-500/10 text-yellow-500 border border-yellow-500/30">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Fake GPS Terdeteksi
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-500/10 text-green-500 border border-green-500/30">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    GPS Asli
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Suspicious Flag -->
                        <div class="bg-gray-800/50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-flag mr-1"></i>Status Validasi</p>
                            <?php if (isset($absensi['is_suspicious']) && $absensi['is_suspicious']): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-red-500/10 text-red-500 border border-red-500/30">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    Mencurigakan
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-500/10 text-green-500 border border-green-500/30">
                                    <i class="fas fa-shield-check mr-2"></i>
                                    Normal
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Validation Notes -->
                    <?php if (isset($absensi['validation_notes']) && $absensi['validation_notes']): ?>
                        <div class="bg-gray-800/50 rounded-lg p-4 mt-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-clipboard-list mr-1"></i>Catatan Validasi</p>
                            <div class="bg-gray-900/50 rounded p-3">
                                <p class="text-xs text-gray-300 leading-relaxed">
                                    <?= nl2br(htmlspecialchars($absensi['validation_notes'])) ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- User Agent -->
                    <?php if (isset($absensi['user_agent']) && $absensi['user_agent']): ?>
                        <div class="bg-gray-800/50 rounded-lg p-4 mt-4">
                            <p class="text-xs text-gray-400 mb-2"><i class="fas fa-info-circle mr-1"></i>User Agent</p>
                            <p class="text-xs font-mono text-gray-400 break-all">
                                <?= htmlspecialchars($absensi['user_agent']) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Security Info Footer -->
                    <div class="bg-teal-500/10 border border-teal-500/30 rounded-lg p-3 mt-4">
                        <p class="text-xs text-teal-400">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Semua data keamanan diambil otomatis saat siswa melakukan absensi untuk mencegah kecurangan.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons - Mobile optimized to stack responsively -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-between animate-fade-in">
                    <div class="flex flex-col xs:flex-row gap-2 sm:gap-3 order-2 sm:order-1">
                        <a href="edit.php?id=<?= $id ?>"
                            class="px-4 py-2.5 sm:py-2 bg-yellow-600 hover:bg-yellow-700 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                            <i class="fas fa-edit"></i> Edit Data
                        </a>
                        <button onclick="confirmDelete(<?= $id ?>)"
                            class="px-4 py-2.5 sm:py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </div>

                    <?php if ($absensi['approval_status'] == 'Pending'): ?>
                        <div class="flex flex-col xs:flex-row gap-2 sm:gap-3 order-1 sm:order-2 mb-3 sm:mb-0">
                            <form method="POST" class="w-full sm:w-auto">
                                <input type="hidden" name="approval_status" value="Approved">
                                <button type="submit" name="approve"
                                    class="w-full px-4 py-2.5 sm:py-2 bg-green-600 hover:bg-green-700 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" class="w-full sm:w-auto">
                                <input type="hidden" name="approval_status" value="Rejected">
                                <button type="submit" name="approve"
                                    class="w-full px-4 py-2.5 sm:py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Back button on mobile only -->
                <div class="mt-6 flex justify-center lg:hidden">
                    <a href="index.php" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-lg flex items-center justify-center text-sm transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Absensi
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal - Mobile optimized -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-[100] hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="hideDeleteModal()"></div>
        <div class="glass-effect rounded-lg p-6 w-11/12 max-w-md relative z-10 mx-4 my-auto animate-fade-in">
            <h3 class="text-xl font-semibold mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-300 mb-6">Apakah Anda yakin ingin menghapus data absensi ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end gap-3">
                <button onclick="hideDeleteModal()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm">
                    Batal
                </button>
                <form method="POST" action="delete.php">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1) ?>assets/js/diome.js"></script>
    <script>
        // Add confirmation modal functions
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Mobile sidebar toggle function
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

        // Update time for mobile view
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

        // Make sure sidebar closes when pressing escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close delete modal if open
                if (!document.getElementById('deleteModal').classList.contains('hidden')) {
                    hideDeleteModal();
                    return;
                }

                // Close sidebar on mobile
                if (window.innerWidth < 1024) {
                    const sidebar = document.getElementById('sidebar');
                    if (!sidebar.classList.contains('-translate-x-full')) {
                        toggleSidebar();
                    }
                }
            }
        });

        // Fix viewport height issues on mobile browsers
        function setMobileHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        window.addEventListener('resize', setMobileHeight);
        setMobileHeight();

        // Add touch device improvements
        document.addEventListener('DOMContentLoaded', function() {
            if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
                // Add visual feedback for touch interactions
                const touchElements = document.querySelectorAll('a, button');
                touchElements.forEach(el => {
                    el.classList.add('touch-target');
                });

                // Enable image preview alternative for touch devices
                const imageLinks = document.querySelectorAll('.image-preview');
                imageLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        // Add a gentle tap effect
                        const img = this.querySelector('img');
                        if (img) {
                            img.style.transition = 'transform 0.2s';
                            img.style.transform = 'scale(0.98)';
                            setTimeout(() => {
                                img.style.transform = 'scale(1)';
                            }, 200);
                        }
                    });
                });
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


    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1) ?>assets/js/theme.js"></script>
</body>

</html>