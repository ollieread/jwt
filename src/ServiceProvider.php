<?php

namespace Ollieread\JWT;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

/**
 * Class ServiceProvider
 *
 * @package Ollieread\JWT
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * @var \Ollieread\JWT\JWT
     */
    protected $jwt;

    public function register()
    {
        $jwt = new JWT();

        // Set the default generator for tokens
        $jwt->setDefaultGenerator(function (Builder $builder, Request $request, Authenticatable $user, string $guard): Builder {
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
        });

        // Set the default validator for tokens
        $jwt->setDefaultValidator(function (Token $token, Request $request, string $guard): bool {
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
        });

        $this->jwt = $jwt;

        // Set the instance
        $this->app->instance(JWT::class, $jwt);
    }

    public function boot()
    {
        // Register/publish the config file
        $this->publishes([
            __DIR__ . '/../config/jwt.php', config_path('jwt.php'),
        ]);

        // Extend auth with our custom guard driver
        Auth::extend('jwt', function ($app, $name, array $config) {
            $guard = new JWTGuard(
                $this->jwt,
                $name,
                Auth::createUserProvider($config['provider'] ?? null),
                $app['request']
            );
            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    /**
     * All config is overridable per guard (except obviously the guards section)
     * Get the specified guards config, and normalise with the default data.
     *
     * @param string $guard
     *
     * @return array
     */
    protected function normaliseConfig(string $guard): array
    {
        $config      = array_except(config('jwt'), ['guards']);
        $guardConfig = config('jwt.guards.' . $guard, []);

        if (empty($guardConfig)) {
            return $config;
        }

        $configDot = array_dot($config);

        foreach ($configDot as $key => $value) {
            if (! array_has($guardConfig, $key)) {
                array_set($guardConfig, $key, $value);
            }
        }

        return $guardConfig;
    }
}