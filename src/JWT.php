<?php

namespace Ollieread\JWT;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;

/**
 * Class JWT
 *
 * @package Ollieread\JWT
 */
class JWT
{

    /**
     * @var \Closure
     */
    protected $generator;

    /**
     * @var array
     */
    protected $guardGenerators = [];

    /**
     * @var \Closure
     */
    protected $validator;

    /**
     * @var \Closure
     */
    protected $guardValidators;

    /**
     * @param string $key
     * @param string $guard
     *
     * @return mixed
     */
    public function config(string $key, string $guard)
    {
        return config('jwt.guards.' . $guard . '.' . $key, config('jwt.' . $key, ''));
    }

    /**
     * @param \Closure $generator
     */
    public function setDefaultGenerator(\Closure $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param \Closure    $generator
     * @param string|null $guard
     */
    public function setGuardGenerator(string $guard, \Closure $generator)
    {
        $this->guardGenerators[$guard] = $generator;
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param \Lcobucci\JWT\Builder                      $builder
     * @param \Illuminate\Http\Request                   $request
     * @param string|null                                $guard
     *
     * @return mixed|null
     */
    public function generate(Authenticatable $user, Builder &$builder, Request $request, string $guard)
    {
        if (isset($this->guardGenerators[$guard])) {
            return call_user_func_array($this->guardGenerators[$guard], compact('builder', 'request', 'user', 'guard'));
        } else if ($this->generator) {
            return call_user_func_array($this->generator, compact('builder', 'request', 'user', 'guard'));
        }

        return null;
    }

    /**
     * @param \Closure $validator
     */
    public function setDefaultValidator(\Closure $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param \Closure    $validator
     * @param string|null $guard
     */
    public function setGuardValidator(string $guard, \Closure $validator)
    {
        $this->guardValidators[$guard] = $validator;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Lcobucci\JWT\Token      $token
     * @param string                   $guard
     *
     * @return mixed
     */
    public function validate(Request $request, Token $token, string $guard)
    {
        if (isset($this->guardValidators[$guard])) {
            return call_user_func_array($this->guardValidators[$guard], compact('token', 'request', 'guard'));
        } else if ($this->validator) {
            return call_user_func_array($this->validator, compact('token', 'request', 'guard'));
        }
    }
}