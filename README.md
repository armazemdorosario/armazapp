## Armazapp

Facebook social app for Armazem do Rosario

## Motivation

We needed a better way to manage VIP Lists and Sweepstakes. There is.

## Files to be ignored when exchanging files through servers

* /config and /cache directories;
* .env file
* (Check other files on .gitignore)

## Installation

* Checkout the source: `git clone git://github.com/armazemdorosario/armazapp.git`
* Run `composer install` to install back-end dependencies;
* Run `bower install` to install front-end dependencies;
* Create a .env file and set `ENV` (development, staging or production) based on .env.example file (included);
* Create or retrieve your Facebook app and set `APP_ID`, `APP_SECRET`, `APP_URL` and `CANVAS_URL` on .env file;
* Still on .env, set relative paths like `CACHE_DIR`, `COMPILE_DIR`, `CONFIGS_DIR` and `TEMPLATE_DIR` for Smarty;
* Set `MAILER_API_KEY` for your Mandrill API key (It's possible that Mandrill doesn't work on development env);
* In /config directory, create an application.php file and set your app meta;
* Still in /config directory, create autoload subdirectory, with global.php and local.php files;
* Create or import a MySQL database;
* Run the app. You'll see database warnings.
* Fill your global.php and local.php files with database dsn, username & password according to the app warnings;
* Bugs? Doubts? Post an issue at https://github.com/armazemdorosario/armazapp/issues/

Armazapp has the following back-end PHP dependencies:
* Dotenv;
* Facebook PHP SDK;
* JaPHPy;
* Mandrill;
* Slim;
* Smarty.

And the following front-end dependencies:
* Switchery;
* Lazyload;
* Bootstrap;
* Modernizr;
* HTML5shiv;
* Respond.

## API Reference

Soon.

## Tests

Soon.

## Contributors

Paulo H. (Jimmy) Andrade Mota C.
