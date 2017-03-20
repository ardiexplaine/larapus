# Larapus - Aplikasi Web Perpustakaan Laravel 5.4
Larapus adalah contoh projek yang dibuat melalui e-book [Seminggu Belajar Laravel](https://leanpub.com/seminggubelajarlaravel) oleh [Rahwat Awaludin](http://facebook.com/rahmat.awaludin).

Karena saat membeli e-book masih menggunakan versi 5.3 jadi saya buat dengan versi 5.4.
Tidak banyak perubahan, hanya upgrade Yajra/Datatables ke v7.0

## Demo
[http://larapus.tk](http://larapus.tk)

Karena menggunakan domain gratis & free account Mailgun maka hanya bisa mengirim e-mail ke e-mail yang di authorized, jika melakukan Sign-Up e-mail baru maka akan mendapatkan error.

Untuk itu silahkan gunakan data berikut untuk login:

- Member:

Username: member@gmail.com

Password: rahasia

- Admin:

Username: admin@gmail.com

Password: rahasia

## Cara Install
1. Download repo ini
2. Ekstrak ke folder
3. Jalankan `composer install` untuk menginstall dependency Laravel (untuk melihat proses instalasi lebih detil, jalankan `composer install -vvv`)
4. Salin file `.env.example` ke `.env`
5. Generate app key untuk aplikasi dengan perintah `php artisan key:generate`
6. Daftar ke [https://www.google.com/recaptcha](https://www.google.com/recaptcha) pada file .env isi NOCAPTCHA_SECRET dan NOCAPTCHA_SITEKEY dengan key yang didapatkan dari [https://www.google.com/recaptcha](https://www.google.com/recaptcha)
7. Daftar ke [http://www.mailgun.com](http://www.mailgun.com). Pada file .env isi MAILGUN_DOMAIN dengan data dari [https://mailgun.com/app/domains](https://mailgun.com/app/domains) dan isi MAILGUN_SECRET dengan data __private api key__ dari [https://mailgun.com/app/account/settings](https://mailgun.com/app/account/settings)
8. Sesuai konfigurasi database
9. Migrasi dan seed database dengan `php artisan migrate:refresh --seed`  
10. Jalankan simple web server dengan `php artisan serve`
11. Akses aplikasi di [http://localhost:8000](http://localhost:8000) (username dan password ada di file __database/seeds/UsersSeeder.php__). Jika ada error "cURL error 60: SSL Certificate probile", kemungkinan ada kesalahan saat konfigurasi Mailgun.

## Bugs
- Email tidak diterima/error?

Pada sample aplikasi ini, kita menggunakan Mailgun sebagai gateway untuk mengirim email. Karena kita menggunakan versi gratis dari Mailgun, hanya email yang diauthorized oleh Mailgun bisa dikirim email. Silahkan lihat dashboard Mailgun Anda.

License [MIT](http://opensource.org/licenses/MIT)
