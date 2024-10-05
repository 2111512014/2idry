## Requirements
* Version 3.1 use PHP 8.0 (Framework Laravel 9) [Versi 3.1](https://github.com/rahmatalmubarak/najmi-laundry.git)
* Database (eg: MySQL)
* Web Server (eg: Apache, Nginx, IIS)

## Framework

Laundry dibangun menggunakan [Laravel](http://laravel.com), the best existing PHP framework, as the foundation framework.

## Installation

* Install [Composer](https://getcomposer.org/download) and [Npm](https://nodejs.org/en/download)
* Clone the repository: `git clone https://github.com/rahmatalmubarak/najmi-laundry.git`
* Install dependencies: `composer install ; npm install ; npm run dev`
* Run `cp .env.example .env` for create .env file
* Run `php artisan migrate --seed` for migration database
* Run `php artisan storage:link` for create folder storage
* Run `php artisan create:admin` for create user Administrator
* Run `php artisan queue:listen` for run queue

</p>

## Package
- [IndoBank](https://github.com/andes2912/indobank) package Laravel untuk menyimpan data Nama Bank yang ada di Indonesia


## Fitur Release
 #### [Versi 3.1](https://github.com/rahmatalmubarak/najmi-laundry.git)
   #### Administrator
   * Dashboard Administrator
   * Tambah User Karyawan
   * Lihat data transaksi
   * Data Finance
   * Data Harga
   * Atur target laundry
   * Ubah thema (untuk saat ini hanya ada Dark & White)
   * Data Bank
   * Setting Notifikasi Email, Telegram dan WhatsAapp
   * Dokumentasi

   #### Karyawan
   * Dashboard Karyawan
   * Data order masuk
   * Data customer
   * Tambah customer
   * Tambah transaksi Laundry
   * Laporan
   * Ubah thema (untuk saat ini hanya ada Dark & White)

   #### Customer
   * Dashboard Customer
   * Ubah thema (untuk saat ini hanya ada Dark & White)
   * Notification List

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
