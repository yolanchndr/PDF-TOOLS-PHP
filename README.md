# PDF-TOOLS-PHP

<p align="center">
  <strong>PDF Tools — Local PDF Processing</strong>
</p>

<p align="center">
  Aplikasi web berbasis PHP untuk menggabungkan, mengatur urutan, dan memisahkan halaman PDF secara lokal.
</p>

<p align="center">



\

</p>

---

## Tentang

**PDF-TOOLS-PHP** adalah aplikasi web sederhana untuk melakukan berbagai operasi dasar terhadap file PDF secara lokal.

Project ini dibuat sebagai alternatif sederhana untuk kebutuhan pengolahan PDF seperti:

* Menggabungkan beberapa file PDF.
* Mengatur urutan file PDF sebelum digabung.
* Mengambil halaman tertentu dari PDF.
* Memisahkan halaman PDF berdasarkan nomor atau range.
* Memproses file PDF secara lokal.

Aplikasi ini terinspirasi dari konsep layanan PDF online seperti iLovePDF, tetapi dirancang untuk dijalankan pada server atau komputer sendiri.

---

## Fitur

### Merge PDF

Menggabungkan beberapa file PDF menjadi satu file.

Contoh:

```text
document-1.pdf
document-2.pdf
document-3.pdf
        ↓
merged.pdf
```

Urutan PDF dapat ditentukan sebelum proses penggabungan.

### Reorder PDF

Mengatur urutan file PDF sebelum digabungkan.

Contoh:

```text
File 1
File 2
File 3
File 4
```

Dapat diatur menjadi:

```text
File 3
File 1
File 4
File 2
```

### Split PDF

Mengambil halaman tertentu dari sebuah file PDF.

Contoh range:

```text
1-3
```

atau:

```text
1-3,5,8
```

Urutan halaman akan mengikuti input yang diberikan.

Contoh:

```text
1-3,5,8-6
```

akan diproses sesuai urutan tersebut.

### Local Processing

File diproses pada server lokal sehingga dokumen tidak perlu dikirim ke layanan PDF pihak ketiga.

Hal ini membuat project cocok untuk:

* Dokumen administrasi.
* Dokumen kantor.
* Arsip pribadi.
* Pengolahan dokumen internal.
* Development dan testing.

---

## Teknologi

| Teknologi | Keterangan                          |
| --------- | ----------------------------------- |
| PHP       | Bahasa pemrograman utama            |
| FPDF      | Library PDF                         |
| FPDI      | Library untuk mengimpor halaman PDF |
| Composer  | Dependency management               |
| Nginx     | Web server                          |
| Laragon   | Local development environment       |

Project membutuhkan **PHP 8.1 atau lebih baru**.

---

## Struktur Project

```text
PDF-TOOLS-PHP/
│
├── public/
│   └── index.php
│
├── src/
│   └── PdfService.php
│
├── storage/
│   ├── uploads/
│   └── outputs/
│
├── .gitignore
├── composer.json
├── composer.lock
└── README.md
```

### `public/`

Berisi entry point aplikasi yang dapat diakses oleh web server.

### `src/`

Berisi logic utama aplikasi PDF.

### `storage/uploads/`

Digunakan untuk menyimpan file PDF yang di-upload sementara.

### `storage/outputs/`

Digunakan untuk menyimpan hasil pemrosesan PDF.

Folder `storage/uploads` dan `storage/outputs` harus dapat ditulis oleh PHP.

---

## Requirements

Sebelum menjalankan project, pastikan sudah tersedia:

* PHP >= 8.1
* Composer
* Nginx atau Apache
* Laragon untuk development lokal

Project ini dapat digunakan pada Windows dengan Laragon maupun environment PHP lainnya yang memenuhi requirement.

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/yolanchndr/PDF-TOOLS-PHP.git
```

Masuk ke folder project:

```bash
cd PDF-TOOLS-PHP
```

### 2. Install Dependency

```bash
composer install
```

### 3. Pastikan Storage Dapat Ditulis

Pastikan folder berikut tersedia:

```text
storage/uploads
storage/outputs
```

Pada Git Bash:

```bash
mkdir -p storage/uploads storage/outputs
```

---

# Menjalankan dengan Laragon

Letakkan project pada:

```text
C:\laragon\www\PDF-TOOLS-PHP
```

Kemudian buka terminal Laragon:

```bash
cd C:\laragon\www\PDF-TOOLS-PHP
```

Install dependency:

```bash
composer install
```

Aktifkan **Nginx** dari Laragon.

Jika Auto Virtual Host aktif, akses:

```text
http://PDF-TOOLS-PHP.test
```

Jika menggunakan konfigurasi folder project dengan nama `pdf-tools`, akses sesuai nama virtual host yang digunakan Laragon.

Alternatif:

```text
http://localhost/PDF-TOOLS-PHP/public
```

Konfigurasi asli repository menggunakan document root ke folder `public`.

---

# Konfigurasi Nginx

Untuk virtual host manual, buat file:

```text
C:\laragon\etc\nginx\sites-enabled\PDF-TOOLS-PHP.test.conf
```

Contoh:

```nginx
server {
    listen 80;

    server_name PDF-TOOLS-PHP.test;

    root "C:/laragon/www/PDF-TOOLS-PHP/public";

    index index.php index.html;

    client_max_body_size 200M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass php_upstream;
    }
}
```

Kemudian restart Nginx dari Laragon.

Repository juga menyediakan konfigurasi Nginx dengan batas upload `200M`.

---

# Menjalankan dengan PHP Built-in Server

Project dapat dijalankan tanpa Nginx menggunakan PHP built-in server:

```bash
php -S localhost:8000 -t public
```

Kemudian buka:

```text
http://localhost:8000
```

---

# Konfigurasi Upload File

Jika ingin memproses file PDF berukuran besar, sesuaikan konfigurasi `php.ini`.

Contoh:

```ini
upload_max_filesize = 200M
post_max_size = 200M
max_execution_time = 300
memory_limit = 512M
```

Setelah mengubah `php.ini`, restart PHP/Nginx atau restart Laragon.

---

# Contoh Penggunaan

## Merge PDF

Misalnya terdapat tiga file:

```text
laporan-01.pdf
laporan-02.pdf
lampiran.pdf
```

Setelah diproses:

```text
laporan-gabungan.pdf
```

Urutan halaman mengikuti urutan file yang dipilih.

---

## Reorder

Misalnya halaman PDF:

```text
1 → 2 → 3 → 4 → 5
```

Urutan baru:

```text
3 → 1 → 5 → 2 → 4
```

Hasil PDF akan mengikuti urutan tersebut.

---

## Split

Untuk mengambil halaman:

```text
1-3,5,8
```

hasilnya akan berisi:

```text
1
2
3
5
8
```

Format range seperti `1-3,5,8-6` juga didukung dan urutan tersebut diproses apa adanya.

---

# Catatan

PDF yang memiliki password atau proteksi tertentu mungkin tidak dapat diproses.

Repository juga mencatat bahwa PDF yang diproteksi password biasanya tidak dapat diproses.

Untuk file PDF berukuran besar, sesuaikan:

```ini
upload_max_filesize
post_max_size
memory_limit
max_execution_time
```

---

# Keamanan

Karena aplikasi menerima file dari pengguna, deployment production sebaiknya menerapkan beberapa langkah keamanan:

* Validasi tipe file.
* Validasi ekstensi PDF.
* Membatasi ukuran upload.
* Menggunakan nama file yang aman.
* Tidak mengizinkan eksekusi PHP di folder upload.
* Membersihkan file temporary secara berkala.
* Membatasi akses terhadap folder `storage`.
* Menghapus file setelah selesai diproses jika sudah tidak diperlukan.

Jangan menyimpan file PDF pengguna ke repository Git.

---

# Roadmap

Pengembangan berikutnya dapat mencakup:

* [ ] PDF Rotate
* [ ] PDF Extract
* [ ] PDF Compress
* [ ] PDF Watermark
* [ ] PDF Password
* [ ] PDF Metadata
* [ ] Drag & Drop Upload
* [ ] PDF Page Preview
* [ ] Progress Bar
* [ ] Batch Processing
* [ ] Automatic Temporary File Cleanup
* [ ] Responsive Mobile UI
* [ ] Dark Mode

---

# Contributing

Kontribusi untuk pengembangan **PDF-TOOLS-PHP** terbuka.

Clone repository:

```bash
git clone https://github.com/yolanchndr/PDF-TOOLS-PHP.git
```

Masuk ke project:

```bash
cd PDF-TOOLS-PHP
```

Install dependency:

```bash
composer install
```

Setelah melakukan perubahan:

```bash
git add .
git commit -m "feat: add new PDF feature"
git push
```

### Conventional Commit

Gunakan format commit yang konsisten:

```text
feat: menambahkan fitur
fix: memperbaiki bug
refactor: merapikan kode
docs: memperbarui dokumentasi
style: memperbaiki tampilan
chore: perubahan konfigurasi
```

---

# License

Project ini mengikuti lisensi yang tercantum pada repository.

---

# Repository

Source code tersedia di:

[PDF-TOOLS-PHP — GitHub](https://github.com/yolanchndr/PDF-TOOLS-PHP?utm_source=chatgpt.com)

---

# Author

**yolanchndr**

---

<p align="center">
  <strong>PDF-TOOLS-PHP</strong><br>
  Local PDF Processing with PHP
</p>
