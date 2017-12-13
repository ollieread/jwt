<?php

return [

    'key'         => env('JWT_KEY', 'changeme'),

    /*
     * Global config for token generation
     */
    'ttl'         => env('JWT_TTL', 60),
    'ttl_refresh' => env('JWT_TTL_REFRESH', 20160),
    'algo'        => env('JWT_ALGO', 'HS256'),
    'claims'      => [
        'required'   => [
            'iss', 'iat', 'exp', 'nbf', 'sub', 'jti', 'grd',
        ],
        'persistent' => [
            'uid', 'grd',
        ],
    ],

    /*
     * Override any of the global config options above for individual guards
     */
    'guards'      => [
        'api' => [
        ],
    ],

];