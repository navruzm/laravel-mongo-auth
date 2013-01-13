MongoAuth
==============

[MongoDB](http://www.mongodb.org/) Authentication driver for [Laravel 4](http://laravel.com/).

Installation
============

Add `navruzm/mongo-auth` as a requirement to composer.json:

```json
{
    "require": {
        "navruzm/mongo-auth": "*"
    }
}
```
And then run `composer update`

Once Composer has updated your packages open up `app/config/app.php` and change `Illuminate\Auth\AuthServiceProvider` to `MongoAuth\MongoAuthServiceProvider`