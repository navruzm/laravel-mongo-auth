[MongoDB](http://www.mongodb.org/) Authentication driver for [Laravel 4](http://laravel.com/).

Installation
============

Add `navruzm/laravel-mongo-auth` as a requirement to composer.json:

```json
{
    "require": {
        "navruzm/laravel-mongo-auth": "*"
    }
}
```
And then run `composer update`

Once Composer has updated your packages open up `app/config/app.php` and change `Illuminate\Auth\AuthServiceProvider` to `MongoAuth\MongoAuthServiceProvider` and `Illuminate\Auth\Reminders\ReminderServiceProvider` to `MongoAuth\Reminders\ReminderServiceProvider`.

Then open `app/config/auth.php` and find the `driver` key and change to `mongo`.