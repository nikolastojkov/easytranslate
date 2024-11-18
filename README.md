# EasyTranslate Code Challenge

An interview code challenge by EasyTranslate.

### Intro

The project uses a simple service/repository pattern with a single action controller.

We store the exchange rate in a cache for 10 seconds in order not to overwhelm Fixer's API and get rate limited.

An optional field `state` can be sent to the API and get be returned as is.

### Setup
- `composer require laravel/sail --dev`
- `php artisan sail:install`
- Add a `FIXER_API_KEY` entry in your .env file
- `./vendor/bin/sail up`

This will setup Laravel's Sail Docker env, and will run it.

You can find your Fixer API Key in the [Dashboard](https://fixer.io/dashboard) after you're logged in