<?php

namespace Ollieread\JWT;

use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Lcobucci\JWT\Builder;

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
        $this->jwt = new JWT();
        $this->jwt->setDefaultGenerator(function (Builder $builder, Request $request, Authenticatable $user, string $guard) {
            $issuedAt = time();
            $builder
                ->setId(str_random(16))
                ->setIssuer($request->getSchemeAndHttpHost())
                ->setIssuedAt($issuedAt)
                ->setAudience($request->getSchemeAndHttpHost())
                ->setExpiration($issuedAt + config('jwt.ttl'))
                ->set('uid', $user->getKey())
                ->set('grd', $guard);
        });
    }
    public function boot()
    {
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
}