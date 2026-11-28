# TUKUO! — Aplikasi PHP

Folder ini berisi versi PHP + MySQL dari website TUKUO!.

## Menjalankan secara lokal
1. Ekstrak atau salin folder ini ke `C:\xampp\htdocs\TUKUO`.
2. Jalankan Apache dan MySQL melalui XAMPP.
3. Buka phpMyAdmin, lalu import `database.sql`.
4. Periksa pengaturan database di `config.php`.
5. Kunjungi `http://localhost/TUKUO/register.php` untuk membuat akun, kemudian login.

## Fitur
- Registrasi, login, dan logout.
- Katalog, pencarian, filter, detail produk, dan keranjang.
- Checkout dan penyimpanan pesanan ke MySQL.
- Tampilan pembayaran QRIS dan konfirmasi manual melalui WhatsApp.

## Catatan deployment dan keamanan
- GitHub Pages hanya menyajikan file statis. Halaman PHP dan database MySQL tidak berjalan di GitHub Pages. Gunakan XAMPP atau hosting yang mendukung PHP dan MySQL.
- Fitur penggantian password pada versi ini tidak memverifikasi kepemilikan email. Gunakan hanya untuk demo lokal sampai reset password berbasis verifikasi email ditambahkan.
- Konfirmasi pembayaran masih manual; aplikasi tidak menerima notifikasi pembayaran otomatis.
