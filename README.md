<div align="center">
  <h1>🎓 Web-Absensi (Revamp)</h1>
  <p>Sistem Manajemen Absensi Digital Terpadu dengan Validasi Jaringan Keamanan Tinggi (IP Whitelist)</p>

  <!-- Badges -->
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</div>

---

## 🚀 Fitur Utama

Aplikasi ini dikembangkan untuk memberikan solusi absensi yang aman, transparan, dan mudah dikelola oleh pihak sekolah/instansi.

### 🛡️ Jaminan Keamanan & Validasi Jaringan
* **Network Whitelist Security (IP Restriction):** Sistem secara otomatis memeriksa IP Address perangkat pengguna. Absensi hanya bisa disubmit jika pengguna terhubung ke jaringan WiFi atau IP Address resmi yang telah didaftarkan.
* **Autentikasi Multi-Role:** Pembatasan hak akses yang ketat antara akun Admin dan akun Siswa.
* **Validasi Data Ketat:** Sistem mendeteksi otomatis jika ada duplikasi NIS (Nomor Induk Siswa) atau kesalahan format saat proses *import*.

### 📝 Sistem Absensi & Pengajuan Mandiri
* **Pencatatan Komprehensif:** Mengelola status absensi harian secara detail (Hadir, Sakit, Izin, Terlambat, Alpha).
* **Pengajuan Absensi & Bukti:** Siswa dapat mengajukan izin/sakit secara mandiri langsung dari portal mereka dengan mengunggah foto/dokumen bukti pendukung.
* **Persetujuan Admin (Approval System):** Admin menerima notifikasi pengajuan baru untuk kemudian disetujui atau ditolak berdasarkan validitas bukti.

### 👥 Manajemen Data & Rekapitulasi Laporan
* **Manajemen Siswa Terstruktur:** Pengelompokan data siswa berdasarkan Kelas dan Jurusan untuk mempermudah pencarian.
* **Mass Import Data:** Fasilitas unggah data siswa secara massal menggunakan file Excel (`.xlsx`, `.xls`), CSV, atau dokumen PDF. Sistem akan memisahkan data yang berhasil dan gagal secara otomatis.
* **Laporan Dinamis & Ekspor PDF:** Cetak rekapitulasi absensi dengan filter fleksibel (rentang tanggal, kelas, jurusan, atau status) ke dalam format PDF siap cetak.

### ⚙️ Fitur Tambahan & UI/UX
* **Desain Responsif:** Antarmuka modern yang optimal diakses lewat smartphone, tablet, maupun laptop/PC.
* **Log Aktivitas (Audit Trail):** Mencatat seluruh tindakan penting yang dilakukan oleh Admin maupun Siswa di dalam sistem demi keamanan data.
* **Profil Kustom:** Pengguna dapat memperbarui informasi pribadi dan mengubah foto profil langsung dari dalam aplikasi.

---

## 🛠️ Teknologi yang Digunakan

* **Front-end:** HTML5, CSS3, JavaScript (ES6), Bootstrap 5
* **Back-end:** PHP 8.1+ (Native)
* **Database:** MySQL 8.0
* **Koneksi Database:** Ekstensi MySQLi
* **Library Eksternal:** DOMPDF (Pembuatan dokumen PDF otomatis)
* **Icon Pack:** Font Awesome

---

## 📋 Persyaratan Sistem

Pastikan lingkungan server (*local server* atau *hosting*) Anda memenuhi kebutuhan berikut:
* PHP versi 8.0 atau yang lebih tinggi
* MySQL versi 8.0 atau yang lebih tinggi
* Web Server (Apache / Nginx)
* Ekstensi PHP wajib aktif: `mysqli`, `gd`, `fileinfo`

---

## 💻 Panduan Penggunaan

### 1. Instalasi dan Persiapan Server
1. *Clone* atau unduh *repository* ini, kemudian letakkan di dalam folder *root* web server Anda (misal: `htdocs` pada XAMPP atau `www` pada Laragon).
2. Jalankan MySQL, lalu buat database baru bernama `db_absensi` (atau nama lain sesuai preferensi).
3. *Import* file database `.sql` yang tersedia di dalam proyek ini ke database baru tersebut.
4. Buka file konfigurasi database di dalam kode proyek, sesuaikan *username*, *password*, dan nama database dengan server Anda.
5. Akses aplikasi melalui browser dengan alamat:
```text
   http://localhost/Web-Absensi

```
### 2. Kredensial Akun (Hak Akses)
 * **Portal Admin:**
   * **Username:** admin
   * **Password:** admin123
 * **Portal Siswa:**
   * Login menggunakan **NIS** masing-masing siswa dan password yang telah diatur/diberikan oleh Admin.
### 3. Alur Kerja Aplikasi (Workflow)
 * **Proses Absensi & Pengajuan (Siswa):**
   Siswa masuk ke portal -> Jika ingin izin/sakit, masuk menu Pengajuan -> Isi keterangan & unggah bukti dokumen -> Kirim. *(Catatan: Untuk status Hadir, pastikan perangkat terhubung ke IP jaringan yang sah, jika tidak akses akan ditolak).*
 * **Verifikasi & Approval (Admin):**
   Admin masuk ke dashboard -> Periksa notifikasi pengajuan masuk -> Cek detail keterangan dan validitas bukti gambar -> Klik Setujui atau Tolak.
 * **Manajemen Data Massal (Admin):**
   Admin masuk menu Data Siswa -> Klik Import -> Pilih file Excel/CSV/PDF -> Sistem melakukan validasi -> Sistem menampilkan ringkasan data yang sukses masuk database serta data yang gagal (karena duplikasi/salah format).
 * **Pencetakan Laporan (Admin):**
   Admin masuk menu Laporan -> Tentukan filter (Tanggal/Kelas/Jurusan) -> Klik Cetak -> File PDF otomatis di-*generate* oleh sistem dan siap diunduh.
<p align="center">Dikembangkan untuk pengelolaan administrasi absensi yang lebih modern dan akurat.</p>
```

```