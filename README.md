# Pixofix Image
A demo web-based application using Laravel that facilitates the management of production orders containing multiple images. The system allow employees to claim and work on a batch of order with ensuring real-time tracking and preventing duplication of work.

### Application Features:
- Laravel 12 + React starter kit
- Spatie Permission Package
- Filament Panel Builder
- Laravel Echo with Pusher Channels

![](https://raw.githubusercontent.com/anikninja/pixofix-image/07683a76eb18303557553d0d93e2dd63632b0373/screenshot.png)

## Demo Video
https://drive.google.com/file/d/1cIpdkUNAQQOubQr5bX-8q5_IiF_VtQRy/view?usp=sharing


## Installation

New Laravel application? make sure that your local machine has `PHP`, `Composer`, `Node`, `NPM` and the `Laravel installer` installed.

Read Doc: [Installing PHP and the Laravel Installer](https://laravel.com/docs/12.x/installation#installing-php)

### Installing PHP and the Laravel Installer

macOS installer:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"
```

Windows installer by Windows PowerShell:

```sh
# Run as administrator...
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))
```

Linux installer:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"
```


Already have PHP and Composer installed? then install the Laravel installer via Composer::

```sh
composer global require laravel/installer
```

### Installation Using Herd

I recommend to [Install Laravel Herd](https://herd.laravel.com/) for local development environment.

*Laravel Herd is considered the best for many due to its ability to simplify local development or test, providing a fast and convenient environment for building Laravel applications.*


## Clone Repository

Clone the repo locally:

```sh
git clone https://github.com/anikninja/pixofix-image.git
cd pixofix-image
```

Install PHP dependencies:

```sh
composer install
```

Install NPM dependencies:

```sh
npm install && npm run build
```

Setup configuration:

```sh
cp .env.example .env
```

Generate application key:

```sh
php artisan key:generate
```

Create an SQLite database. You can also use another database (MySQL, Postgres), simply update your configuration accordingly.

```sh
touch database/database.sqlite
```

Run database migrations:

```sh
php artisan migrate
```

Run database seeder:

```sh
php artisan db:seed
```

Run server:

```sh
composer run dev
```

*Click on following `APP_URL` in your Terminal.*

N:B: If you are using **Laravel Herd**, then you should set your Project Name: `pixofix-image`

Change the `APP_URL` value `http://pixofix-image.test` in your local `.env` file.


## Login credentials
### login as Admin:
- **Username:** `admin@example.com`
- **Password:** `password`
### login as Employee:
- **Username:** `employee1@example.com`  or `employee2@example.com` or `employee3@example.com`
- **Password:** `password`


## Running tests

To run the Pest tests, run:

```
php artisan test
```


## Credits

🚀 Original work by Anik [@anikninja](https://www.github.com/anikninja)

[![linkedin](https://img.shields.io/badge/linkedin-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/anik89bd/)
