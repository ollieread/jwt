<?php

namespace Ollieread\JWT;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Lcobucci\JWT\Builder;

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
    protected $claimGenerator;

    /**
     * @var array
     */
    protected $claimGuardGenerators = [];

    /**
     * @param \Closure $closure
     */
    public function setDefaultGenerator(\Closure $closure)
    {
        $this->claimGenerator = $closure;
    }

    /**
     * @param \Closure    $generator
     * @param string|null $guard
     */
    public function setGuardGenerator(\Closure $generator, string $guard)
    {
        if ($guard) {
            $this->claimGuardGenerators[$guard] = $generator;
        } else {
            $this->claimGenerator = $generator;
        }
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param \Lcobucci\JWT\Builder                     $builder
     * @param \Illuminate\Http\Request                  $request
     * @param string|null                               $guard
     *
     * @return mixed|null
     */
    public function generateClaim(Authenticatable $user, Builder &$builder, Request $request, string $guard = null)
    {
        if (isset($this->claimGuardGenerators[$guard])) {
            return call_user_func_array($this->claimGuardGenerators[$guard], compact('builder', 'request', 'user', 'guard'));
        } else if ($this->claimGenerator) {
            return call_user_func_array($this->claimGenerator, compact('builder', 'request', 'user', 'guard'));
        }

        return null;
    }
}