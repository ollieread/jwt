<?php

return [

    /*
     * This key is used for Hmac signing
     */
    'key'           => env('JWT_KEY', 'changeme'),

    /*
     * Global config for token generation
     */
    /*
     * The amount of time a token should last
     */
    'ttl'           => env('JWT_TTL', 86400),
    /*
     * The amount of time that a token can be refreshed (currently not used)
     */
    'ttl_refresh'   => env('JWT_TTL_REFRESH', 20160),
    /*
     * The algorithm to use for signing the token
     */
    'algo'          => env('JWT_ALGO', \Lcobucci\JWT\Signer\Hmac\Sha256::class),
    /*
     * The prefix on the authorization header used for reading the token
     */
    'header_prefix' => env('JWT_HEADER_PREFIX', 'bearer'),
    /*
     * Specific claim settings
     */
    'claims'        => [
        /*
         * This is used to validate tokens
         */
        'required'   => [
            'iss', 'iat', 'exp', 'nbf', 'sub', 'jti', 'grd',
        ],
        /*
         * This is user to persist claims when refreshing (currently not used)
         */
        'persistent' => [
            'uid', 'grd',
        ],
    ],

    /*
     * Override any of the global config options above for individual guards
     */
    'guards'        => [
        'api' => [
        ],
    ],

];