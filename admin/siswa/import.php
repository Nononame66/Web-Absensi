<?php
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success_msg = '';
$warning_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    try {
        $file = $_FILES['excel_file'];
        
        // Validate file type
        $allowed_extensions = ['xls', 'xlsx'];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            throw new Exception("Format file tidak didukung. Harap unggah file .xls atau .xlsx");
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception("Ukuran file terlalu besar. Maksimal 5MB.");
        }
        
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // 1. Extract Class and Major from B4
        $kelasRaw = $worksheet->getCell('B4')->getValue();
        if (empty($kelasRaw)) {
            throw new Exception("Format file tidak valid: Data kelas di B4 tidak ditemukan.");
        }
        
        // Parsing KELAS : X RPL-1 -> Kelas 10, Jurusan RPL
        $kelas = '10';
        $jurusan = 'RPL';
        
        $kelasRawUpper = strtoupper($kelasRaw);
        if (strpos($kelasRawUpper, 'XII') !== false) {
            $kelas = '12';
        } elseif (strpos($kelasRawUpper, 'XI') !== false) {
            $kelas = '11';
        } elseif (strpos($kelasRawUpper, 'X') !== false) {
            $kelas = '10';
        }
        
        $jurusans = ['RPL', 'DKV', 'AK', 'BR', 'MP'];
        foreach ($jurusans as $j) {
            if (strpos($kelasRawUpper, $j) !== false) {
                $jurusan = $j;
                break;
            }
        }
        
        // Begin Transaction
        $conn->beginTransaction();
        
        $startRow = 7;
        $highestRow = $worksheet->getHighestDataRow();
        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $processedStudents = 0; // Menghitung total siswa yang diproses
        
        for ($row = $startRow; $row <= $highestRow; $row++) {
            // Berhenti jika sudah memproses 40 siswa
            if ($processedStudents >= 40) {
                break;
            }
            
            $no = trim($worksheet->getCell('A' . $row)->getValue() ?? '');
            $nis = trim($worksheet->getCell('B' . $row)->getValue() ?? '');
            $nama = trim($worksheet->getCell('C' . $row)->getValue() ?? '');
            $jk = trim($worksheet->getCell('D' . $row)->getValue() ?? '');
            
            // If NIS and Nama are empty, we might have hit the footer
            if (empty($nis) && empty($nama)) {
                // Peek ahead to check if next row is also empty to confirm footer
                $nextNis = trim($worksheet->getCell('B' . ($row + 1))->getValue() ?? '');
                if (empty($nextNis)) {
                    break; 
                }
                continue;
            }
            
            // If No is empty but there's a footer text, also break
            if (empty($no) && (strpos(strtolower($nis), 'laki-laki') !== false || strpos(strtolower($nis), 'perempuan') !== false || strpos(strtolower($nis), 'mengetahui') !== false)) {
                break;
            }
            
            // Skip rows without valid NIS
            if (empty($nis)) {
                continue;
            }
            
            // Normalize JK
            $jk = strtoupper($jk);
            if ($jk !== 'L' && $jk !== 'P') {
                $jk = 'L'; // Default if unparseable
            }
            
            // Generate Email and Password
            // Replace non-alphanumeric chars for email
            $cleanNis = preg_replace('/[^a-zA-Z0-9]/', '', $nis);
            $email = strtolower($cleanNis) . '@siswa.smkn1sanden.sch.id';
            $password = 'siswa_' . $cleanNis;
            $foto_profil = 'assets/default/photo-profile.png';
            
            // Check if NIS already exists
            $check_sql = "SELECT id FROM siswa WHERE nis = :nis";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute(['nis' => $nis]);
            $existingSiswa = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingSiswa) {
                // Update
                $update_sql = "UPDATE siswa SET nama_lengkap = :nama, jenis_kelamin = :jk, kelas = :kelas, jurusan = :jurusan WHERE nis = :nis";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->execute([
                    'nama' => $nama,
                    'jk' => $jk,
                    'kelas' => $kelas,
                    'jurusan' => $jurusan,
                    'nis' => $nis
                ]);
                $updatedCount++;
            } else {
                // Insert
                $insert_sql = "INSERT INTO siswa (nis, nama_lengkap, jenis_kelamin, kelas, jurusan, email, password, foto_profil) 
                              VALUES (:nis, :nama, :jk, :kelas, :jurusan, :email, :password, :foto_profil)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->execute([
                    'nis' => $nis,
                    'nama' => $nama,
                    'jk' => $jk,
                    'kelas' => $kelas,
                    'jurusan' => $jurusan,
                    'email' => $email,
                    'password' => $password,
                    'foto_profil' => $foto_profil
                ]);
                $insertedCount++;
            }
            
            $processedStudents++; // Tambah counter siswa yang berhasil dibaca
        }
        
        $conn->commit();
        
        // Log activity
        $sql = "INSERT INTO activity_log (user_type, user_id, activity_type, description) 
                VALUES ('admin', :admin_id, 'create', :description)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'admin_id' => $_SESSION['admin_id'],
            'description' => "Admin mengimpor data siswa Excel Kelas $kelas $jurusan: $insertedCount baru, $updatedCount diupdate"
        ]);
        
        $success_msg = "Data berhasil diimpor! $insertedCount siswa baru ditambahkan, $updatedCount siswa diperbarui.";
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Siswa - SMKN 1 Sanden</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        
        .file-drop-area {
            border: 2px dashed rgba(20, 184, 166, 0.5);
            background-color: rgba(20, 184, 166, 0.05);
            transition: all 0.3s;
        }
        
        .file-drop-area.dragover {
            background-color: rgba(20, 184, 166, 0.15);
            border-color: rgba(20, 184, 166, 1);
        }
    </style>
</head>

<body class="min-h-screen text-white bg-fixed">
    <!-- Side Navigation omitted for brevity in import page -->
    <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 glass-effect border-r border-teal-500/20 z-50 hidden lg:block">
        <div class="flex items-center justify-between p-4 lg:p-6 border-b border-teal-500/20">
            <div class="flex items-center gap-3">
                <img src="../../assets/default/smkn1sanden.png" alt="SMKN 1 Sanden" class="h-8 lg:h-10 w-auto">
                <div>
                    <h1 class="font-semibold text-sm lg:text-base">SMKN 1 Sanden</h1>
                    <p class="text-xs text-gray-400">Sistem Absensi</p>
                </div>
            </div>
        </div>

        <nav class="p-4 space-y-2 overflow-y-auto" style="max-height: calc(100vh - 76px);">
            <a href="../dashboard/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="../absensi/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-500/10 transition-colors">
                <i class="fas fa-calendar-check"></i>
                <span>Absensi</span>
            </a>
            <a href="index.php" class="flex items-center gap-3 text-white/90 p-3 rounded-lg menu-active">
                <i class="fas fa-users text-teal-400"></i>
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
        </nav>
    </aside>

    <main class="lg:ml-64 min-h-screen p-4 lg:p-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center mb-6">
                <a href="index.php" class="mr-3 p-2 rounded-full hover:bg-gray-800/60 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold">Import Data Siswa</h1>
                    <p class="text-sm md:text-base text-gray-400">Unggah file Excel (.xlsx) untuk menambahkan siswa secara massal</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-500 rounded-lg p-4 mb-6 flex items-start">
                    <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
                    <div>
                        <p class="font-medium">Proses Import Gagal</p>
                        <p class="text-sm text-red-500/80 mt-1"><?= $error ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success_msg): ?>
                <div class="bg-green-500/10 border border-green-500/30 text-green-500 rounded-lg p-4 mb-6 flex items-start">
                    <i class="fas fa-check-circle mt-0.5 mr-3"></i>
                    <div>
                        <p class="font-medium">Import Berhasil</p>
                        <p class="text-sm text-green-500/80 mt-1"><?= $success_msg ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="glass-effect rounded-xl p-6">
                <div class="mb-6 bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                    <h3 class="font-semibold text-teal-400 mb-2"><i class="fas fa-info-circle mr-2"></i>Petunjuk Format File</h3>
                    <ul class="list-disc list-inside text-sm text-gray-300 space-y-1">
                        <li>File harus berekstensi <strong>.xls</strong> atau <strong>.xlsx</strong></li>
                        <li><strong>Cell B4</strong> harus berisi keterangan kelas, contoh: <code>KELAS : X RPL-1</code></li>
                        <li>Data siswa dimulai dari <strong>Baris 8</strong>.</li>
                        <li>Kolom wajib: <strong>Kolom B</strong> (NIS), <strong>Kolom C</strong> (Nama Lengkap), <strong>Kolom D</strong> (Jenis Kelamin L/P).</li>
                        <li>Baris yang tidak valid (NIS kosong/teks non-siswa) akan diabaikan secara otomatis.</li>
                    </ul>
                </div>

                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="file-drop-area rounded-xl p-10 text-center cursor-pointer mb-6 relative" id="dropArea" onclick="document.getElementById('excel_file').click()">
                        <input type="file" name="excel_file" id="excel_file" class="hidden" accept=".xls,.xlsx" required onchange="handleFileSelect(this)">
                        <i class="fas fa-cloud-upload-alt text-4xl text-teal-500 mb-4"></i>
                        <h3 class="text-lg font-medium mb-2" id="fileNameHeading">Pilih File atau Tarik ke Sini</h3>
                        <p class="text-gray-400 text-sm" id="fileDesc">Mendukung format .xlsx dan .xls (Max 5MB)</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-6 py-2.5 bg-teal-500 hover:bg-teal-600 text-white rounded-lg transition-colors font-medium flex items-center shadow-lg" id="submitBtn">
                            <i class="fas fa-file-import mr-2"></i> Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('excel_file');
        const fileNameHeading = document.getElementById('fileNameHeading');
        const fileDesc = document.getElementById('fileDesc');
        const form = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');

        // Handle drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.classList.add('dragover');
        }

        function unhighlight() {
            dropArea.classList.remove('dragover');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                fileInput.files = files;
                updateFileInfo(files[0]);
            }
        }

        function handleFileSelect(input) {
            if (input.files.length) {
                updateFileInfo(input.files[0]);
            }
        }

        function updateFileInfo(file) {
            fileNameHeading.textContent = file.name;
            fileNameHeading.classList.add('text-teal-400');
            const size = (file.size / 1024 / 1024).toFixed(2);
            fileDesc.textContent = `${size} MB`;
        }

        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        });
    </script>
    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 1) ?>assets/js/theme.js"></script>
</body>
</html>
