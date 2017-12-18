# Laravel JWT

This package provides a driver for Laravel auth allowing developers to make use of JWT (JSON Web Tokens).

The reason for its creation was that there aren't really many packages out there that do this, and work. The most popular of the existing packages is out of date, using a JWT library that has been discontinued, and doesn't seem to be updated that often. On top of that, it doesn't integrate exactly with Laravels auth functionality. It's also somewhat over complicated.

## Dependencies

- Laravel 5.5+
- PHP 7.1
- OpenSSL Extension

## Installation

Package is available on [Packagist](https://packagist.org/packages/ollieread/laravel-jwt), you can install it using Composer.

    composer require ollieread/laravel-jwt

## Configuration



## Usage

## Information

See [RFC 7519](https://tools.ietf.org/html/rfc7519) for more information about JWT.

This package uses the [lcobucci/jwt](https://packagist.org/packages/lcobucci/jwt) package to generate the tokens.
