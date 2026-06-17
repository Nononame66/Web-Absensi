<?php
require_once '../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kelas']) && isset($_POST['jurusan'])) {
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];

    if (empty($kelas) || empty($jurusan)) {
        header("Location: index.php?delete=error&message=" . urlencode("Pilih kelas dan jurusan yang valid."));
        exit();
    }

    try {
        // Start transaction
        $conn->beginTransaction();

        // Get count of students
        $sqlCount = "SELECT COUNT(*) FROM siswa WHERE kelas = :kelas AND jurusan = :jurusan";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->execute(['kelas' => $kelas, 'jurusan' => $jurusan]);
        $count = $stmtCount->fetchColumn();

        if ($count > 0) {
            // Delete the students (absensi will be deleted via CASCADE if set, or just delete students)
            // It is safer to delete from absensi first explicitly
            $sqlDeleteAbsensi = "DELETE FROM absensi WHERE siswa_id IN (SELECT id FROM siswa WHERE kelas = :kelas AND jurusan = :jurusan)";
            $stmtDeleteAbsensi = $conn->prepare($sqlDeleteAbsensi);
            $stmtDeleteAbsensi->execute(['kelas' => $kelas, 'jurusan' => $jurusan]);

            // Delete students
            $sqlDeleteSiswa = "DELETE FROM siswa WHERE kelas = :kelas AND jurusan = :jurusan";
            $stmtDeleteSiswa = $conn->prepare($sqlDeleteSiswa);
            $stmtDeleteSiswa->execute(['kelas' => $kelas, 'jurusan' => $jurusan]);

            // Log activity
            $description = "Admin menghapus seluruh data siswa Kelas $kelas $jurusan (Total: $count siswa)";
            $sqlLog = "INSERT INTO activity_log (user_type, user_id, activity_type, description) 
                       VALUES ('admin', :admin_id, 'delete', :description)";
            $stmtLog = $conn->prepare($sqlLog);
            $stmtLog->execute([
                'admin_id' => $_SESSION['admin_id'],
                'description' => $description
            ]);

            $conn->commit();
            header("Location: index.php?delete=success&msg=" . urlencode("Berhasil menghapus $count siswa Kelas $kelas $jurusan"));
            exit();
        } else {
            throw new Exception("Tidak ada siswa di Kelas $kelas $jurusan");
        }
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: index.php?delete=error&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Invalid request
    header("Location: index.php");
    exit();
}
