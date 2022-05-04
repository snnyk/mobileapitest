# Test API + Worker
Installation

```sh
git clone https://github.com/snnyk/teknasyon.git .
```
```sh
composer install
```
Set your mysql and redis connection in .env file
```sh
php artisan migrate
```
```sh
php artisan serve
```


Subscription Check Command

```sh
php artisan subscriptions:check
```
```sh
php artisan queue:work
```
