<?php

namespace Ollieread\JWT;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

/**
 * Class JWTGuard
 *
 * @package Ollieread\JWT
 */
class JWTGuard implements Guard
{
    use GuardHelpers;

    /**
     * @var \Ollieread\JWT\JWT
     */
    protected $jwt;

    /**
     * @var string
     */
    protected $name;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The current token.
     *
     * @var \Lcobucci\JWT\Token
     */
    protected $token;

    /**
     * JWTGuard constructor.
     *
     * @param \Ollieread\JWT\JWT                      $jwt
     * @param string                                  $name
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \Illuminate\Http\Request                $request
     */
    public function __construct(JWT $jwt, string $name, UserProvider $provider, Request $request)
    {
        $this->jwt      = $jwt;
        $this->name     = $name;
        $this->provider = $provider;
        $this->request  = $request;
    }

    /**
     * @return \Lcobucci\JWT\Token|null
     * @throws \Ollieread\JWT\InvalidTokenException
     */
    public function token(): ?Token
    {
        return $this->token ?? $this->getTokenForRequest();
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     *
     * @throws \Ollieread\JWT\InvalidTokenException
     */
    public function setToken(Token $token)
    {
        $this->token = $token;

        if (! $this->validateToken()) {
            throw new InvalidTokenException('Invalid token');
        }
    }

    /**
     * @return \Lcobucci\JWT\Token
     * @throws \Ollieread\JWT\InvalidTokenException
     */
    public function getTokenForRequest()
    {
        $header = $this->request->headers->get('authorization') ?: $this->request->server->get('HTTP_AUTHORIZATION');
        $headerPrefix = $this->jwt->config('header_prefix', $this->name);

        if ($header && stripos($header, $headerPrefix) === 0) {
            $headerParts = explode(' ', $header);
            $this->setToken((new Parser)->parse($headerParts[1]));

            return $this->token;
        }

        return null;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \Ollieread\JWT\InvalidTokenException
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        if (is_null($this->token)) {
            $this->getTokenForRequest();
        }

        if ($this->token) {
            $user = $this->provider->retrieveById($this->token->getClaim('uid'));
        }

        return $this->user = $user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return ! is_null($this->provider->retrieveByCredentials($credentials));
    }

    /**
     * @return bool
     */
    public function validateToken(): bool
    {
        if ($this->token) {
            return $this->jwt->validate($this->request, $this->token, $this->name);
        }

        return false;
    }

    /**
     * @param array $credentials
     * @param bool  $login
     *
     * @return bool
     */
    public function attempt(array $credentials, bool $login = true): bool
    {
        $this->user = $this->provider->retrieveByCredentials($credentials);

        if ($this->user) {
            return $login ? ! is_null($this->login()) : true;
        }

        return false;
    }

    /**
     * @return \Lcobucci\JWT\Token|null
     */
    public function login(): ?Token
    {
        if ($this->user) {
            $builder = new Builder;
            $this->jwt->generate($this->user, $builder, $this->request, $this->name);

            return $this->token = $builder->getToken();
        }

        return null;
    }

    public function refresh()
    {
        if ($this->token) {

        }
    }
}