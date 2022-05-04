# Test API + Worker
Installation

```sh
git clone https://github.com/snnyk/teknasyon.git .
```
```sh
composer install
```
```sh
php artisan serve
```
Don't forget to set your mysql and redis connetion in .env file

Subscription Check Command

```sh
php artisan subscriptions:check
```
```sh
php artisan queue:work
```