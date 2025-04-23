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

You're ready to go! [http://pixofix-image.test/] in your browser.
## Login credentials
### login as Admin:
- **Username:** `admin@example.com`
- **Password:** `password`
### login as Employee:
- **Username:** `employee1@example.com`  or `employee2@example.com` or `employee3@example.com`
- **Password:** `password`


## Running Queue

To run the queue for real-time notification, run:

```
php artisan queue:work
```

## Running tests

To run the Pest tests, run:

```
php artisan test
```

## Credits

🚀 Original work by Anik [@anikninja](https://www.github.com/anikninja)

[![linkedin](https://img.shields.io/badge/linkedin-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/anik89bd/)

## License

[MIT](https://choosealicense.com/licenses/mit/)

