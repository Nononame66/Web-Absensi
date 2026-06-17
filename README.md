# Web-Absensi
apain sikit biar ga apa kali

## 🚀 Fitur

-   🔐 Sistem autentikasi yang aman untuk admin dan siswa
-   📝 Pengelolaan data absensi harian (hadir, sakit, izin, terlambat, alpha)
-   👨‍🎓 Manajemen data siswa berdasarkan kelas dan jurusan
-   ✅ Siswa dapat melakukan pengajuan absensi dengan bukti pendukung
-   ⚠️ Admin dapat menyetujui atau menolak pengajuan absensi
-   📊 Laporan absensi dengan berbagai filter (tanggal, kelas, jurusan)
-   📱 Desain responsif untuk berbagai ukuran perangkat
-   🔄 Log aktivitas untuk melacak tindakan pengguna dalam sistem
-   👤 Manajemen profil pengguna termasuk foto profil

## 🛠️ Teknologi yang Digunakan

-   **Front-end**: HTML5, CSS3, JavaScript, Bootstrap 5
-   **Back-end**: PHP 8.1+
-   **Database**: MySQL 8.0
-   **Library**: DOMPDF untuk generasi laporan PDF
-   **Framework CSS**: Font Awesome untuk ikon
-   **Koneksi Database**: MySQLi untuk pengelolaan database

## 📋 Persyaratan Sistem

-   PHP 8.0 atau lebih tinggi
-   MySQL 8.0 atau lebih tinggi
-   Web server (Apache/Nginx)
-   Ekstensi PHP: MySQLi, GD, FileInfo

## 💻 Cara Penggunaan

### Login Admin

-   Gunakan username `admin` dan password `admin123` untuk masuk ke panel admin
-   Kelola data siswa, absensi, dan laporan melalui menu yang tersedia

### Login Siswa

-   Siswa menggunakan NIS dan password yang telah diberikan untuk login
-   Siswa dapat mengajukan absensi dan melihat riwayat absensi mereka

### Pengelolaan Absensi

1. **Pengajuan Absensi oleh Siswa**:

    - Siswa login ke portal siswa
    - Pilih status absensi (Hadir, Sakit, Izin)
    - Isi keterangan jika diperlukan
    - Upload bukti pendukung (untuk sakit atau izin)
    - Kirim pengajuan untuk ditinjau oleh admin

2. **Persetujuan Absensi oleh Admin**:

    - Admin menerima notifikasi pengajuan absensi baru
    - Admin dapat melihat detail pengajuan termasuk bukti
    - Admin menyetujui atau menolak pengajuan tersebut

3. **Laporan Absensi**:

    - Admin dapat melihat laporan absensi siswa
    - Filter berdasarkan tanggal, kelas, jurusan, atau status absensi
    - Ekspor laporan dalam format PDF

4. **Manajemen Siswa**:

    - Admin dapat menambah, mengedit, dan menghapus data siswa
    - Mengatur kelas dan jurusan siswa
    - Mengatur akses login siswa

5. **Log Aktivitas**:
    - Admin dapat melihat log aktivitas sistem
    - Memantau tindakan yang dilakukan oleh admin dan siswa