<?php

namespace Ollieread\JWT;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package Ollieread\JWT
 */
class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            $guard = new JWTGuard(
                $name,
                Auth::createUserProvider($config['provider'] ?? null),
                $app['request']
            );
            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}