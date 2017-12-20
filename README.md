# Laravel JWT

[![Latest Stable Version](https://poser.pugx.org/ollieread/laravel-jwt/v/stable.png)](https://packagist.org/packages/ollieread/laravel-jwt) [![Total Downloads](https://poser.pugx.org/ollieread/laravel-jwt/downloads.png)](https://packagist.org/packages/ollieread/laravel-jwt) [![Latest Unstable Version](https://poser.pugx.org/ollieread/laravel-jwt/v/unstable.png)](https://packagist.org/packages/ollieread/laravel-jwt) [![License](https://poser.pugx.org/ollieread/laravel-jwt/license.png)](https://packagist.org/packages/ollieread/laravel-jwt)


This package provides a driver for Laravel auth allowing developers to make use of JWT (JSON Web Tokens).

The reason for its creation was that there aren't really many packages out there that do this, and work. The most popular of the existing packages is out of date, using a JWT library that has been discontinued, and doesn't seem to be updated that often. On top of that, it doesn't integrate exactly with Laravels auth functionality. It's also somewhat over complicated.

## Dependencies

- Laravel 5.5+
- PHP 7.1
- OpenSSL Extension

## Installation

Package is available on [Packagist](https://packagist.org/packages/ollieread/laravel-jwt), you can install it using Composer.

    composer require ollieread/laravel-jwt
    
Next you want to publish the configuration.

    php artisan vendor:publish 
    
To use JWT for your auth, set the driver to `jwt`, like so.

    'guards' => [
        'web' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ]

## Configuration

Since this package is customisable, the majority of the configuration only exists to support the default implementations. 
The config file itself contains explanations of what each option is.

| Option | Description |
| ------ | ----------- |
| `key` | Used for HMAC signing |
| `ttl` | The total time, in seconds, a token should last for |
| `ttl_refresh` | The total time, in seconds, that a token can be refreshed (not currently used) |
| `algo` | The algorithm class to use for signing the token |
| `header_prefix` | The prefix in the Authorization header, typically `bearer`. This is the only option that is hard coded to be required |
| `claims.required` | This is used to validate a token, making sure that the claims exists |
| `claims.persistent` | The claims to be used when refreshing the token (not currently used) |

The options after this take the following format.

    'guards'        => [
        'api' => [
        ],
    ],
    
All of the configuration options in the table above (or any that you add yourself) can be overridden in here, per guard. This lets you have a different key or even algorithm per guard.

## Usage

This works the same way that the default auth does, for the most part.

### Logging in

Logging a user in is the same as you would with session drivers.

Credentials:

    $credentials = $request->all(['email', 'password']);
    
    if (Auth::attempt($credentials)) {
        $token = Auth::token();
        return $token;
    }
    
Instance of your user model:

    $token = Auth::setUser($user)->login();
    
### Checking login status

    Auth::check()
    
### Getting current user

    Auth::user()
    
## Token Generation

This package comes with a default implementation, but you're able to override that, either globally or per guard.

There isn't a facade for this, mostly because I don't like facades. Instead you can type hint `Ollieread\JWT\JWT` or just use `app(Ollieread\JWT\JWT::class)`.

The generation uses the builder provided by the lcobucci/jwt library, which has documentation here: https://github.com/lcobucci/jwt/blob/3.2/README.md

### Example Generator

    function (Builder $builder, Request $request, Authenticatable $user, string $guard, ?Token $token): Builder {
        // Get the normalised config for this guard
        $config = $this->normaliseConfig($guard);
    
        // Build the token
        return $builder
            ->setId(str_random(16))
            ->setIssuer($request->getSchemeAndHttpHost())
            ->setIssuedAt($issuedAt = time())
            ->setAudience($request->getSchemeAndHttpHost())
            ->setExpiration($issuedAt + $config['ttl'])
            ->set('uid', $user->getAuthIdentifier())
            ->set('grd', $guard)
            ->sign(new $config['algo'], $config['key']);
    }

### Global

To set the default generator, use `JWT::setDefaultGenerator()`. This takes an instance of `\Closure` like below.

    // Set the default generator for tokens
    $jwt->setDefaultGenerator(the generator);
    
### Per Guard

To set the guard generator, use `JWT::setGuardGenerator()`. This takes an string referencing the guard that this is for, and an instance of `\Closure` like below.

    // Set the default generator for tokens
    $jwt->setGuardGenerator('myguard', the generator);

## Validation

This package comes with a default implementation, but you're able to override that, either globally or per guard.

There isn't a facade for this, mostly because I don't like facades. Instead you can type hint `Ollieread\JWT\JWT` or just use `app(Ollieread\JWT\JWT::class)`.

There is a ValidationData available from the lcobucci/jwt library, which has documentation here: https://github.com/lcobucci/jwt/blob/3.2/README.md

### Example Validator

    function (Token $token, Request $request, string $guard): bool {
        // Get the normalised config for this guard
        $config = $this->normaliseConfig($guard);
        // Check that the required claims are present
        foreach ($config['claims']['required'] as $claim) {
            if (! $token->hasClaim($claim)) {
                return false;
            }
        }
        // Check that the grd claim matches the current guard
        // We don't check for its presence as it's in the default required claims
        if ($token->getClaim('grd') !== $guard) {
            return false;
        }
        // Verify the signature of the token
        if (! $token->verify(new Sha256, $config['key'])) {
            return false;
        }
        // All of the above has passed, now run the validator on it
        $validator = new ValidationData;
        $validator->setIssuer($request->getSchemeAndHttpHost());
        $validator->setAudience($request->getSchemeAndHttpHost());

        // Return the validation result
        return $token->validate($validator);
    }

### Global

To set the default validator, use `JWT::setDefaultValidator()`. This takes an instance of `\Closure` like below.

    // Set the default validator for tokens
    $jwt->setDefaultValidator(the validator);
    
### Per Guard

To set the guard generator, use `JWT::setGuardValidator()`. This takes an string referencing the guard that this is for, and an instance of `\Closure` like below.

    // Set the default generator for tokens
    $jwt->setGuardValidator('myguard', the validator);

## Information

See [RFC 7519](https://tools.ietf.org/html/rfc7519) for more information about JWT.

This package uses the [lcobucci/jwt](https://packagist.org/packages/lcobucci/jwt) package to generate the tokens.
