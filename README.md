# TIBER - Backend (Tuberculosis Care App)
- Dev branch contains development demo that can run
- feat/"feature name" contains development features

## Deskripsi Singkat
Sistem Back-end ini dibangun menggunakan **Laravel** dengan database **MySQL**. Sistem ini ditujukan untuk penggunaan Front-end via API.

## How To Run

### 0. Requirements
Make sure you have these things ready and working:
- PHP
- Laravel
- A database server or you can simply run it locally using XAMPP, laragon, etc.

### 1. Adding The .env File
Copy the existing .env.example file and modify it according to your needs
Here are the example

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tiber
DB_USERNAME=root
DB_PASSWORD=
```

Save it and rename it to just .env

### 2. Generate The Autoload Files
To generate autoload files on terminal simply, go to the directory of this file is in and run

```bash
composer update
```

And wait until it is finished

### 3. Running The Project
Then finally run this to start

```bash
php artisan serve
```

