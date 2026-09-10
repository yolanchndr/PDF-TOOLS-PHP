# Local PDF Tools

Aplikasi lokal sederhana seperti iLovePDF untuk:

- Menggabungkan beberapa PDF.
- Mengatur urutan PDF sebelum digabung.
- Memisahkan / mengambil halaman PDF berdasarkan urutan atau range.

## Cara Pasang di Laragon

1. Pastikan Laragon memakai PHP 8.1 atau lebih baru.
2. Salin folder project ini ke:

   ```txt
   C:\laragon\www\pdf-tools
   ```

3. Buka terminal Laragon di folder project:

   ```powershell
   cd C:\laragon\www\pdf-tools
   composer install
   ```

4. Pastikan folder berikut bisa ditulis PHP:

   ```txt
   storage\uploads
   storage\outputs
   ```

5. Di Laragon, aktifkan Nginx lalu akses:

   ```txt
   http://pdf-tools.test
   ```

   Jika auto virtual host belum aktif, buka:

   ```txt
   http://localhost/pdf-tools/public
   ```

## Konfigurasi Nginx Manual

Kalau ingin virtual host manual, buat file:

```txt
C:\laragon\etc\nginx\sites-enabled\pdf-tools.test.conf
```

Isi:

```nginx
server {
    listen 80;
    server_name pdf-tools.test;
    root "C:/laragon/www/pdf-tools/public";
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

Restart Nginx dari Laragon setelah mengubah konfigurasi.

## Catatan

- PDF yang diproteksi password biasanya tidak bisa diproses.
- Untuk upload besar, naikkan `upload_max_filesize` dan `post_max_size` di `php.ini`.
- Range halaman bisa ditulis seperti `1-3,5,8-6`. Urutan tersebut akan dipakai apa adanya.
