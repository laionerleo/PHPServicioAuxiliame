<?php

return [
    'secret' => env('JWT_SECRET', 'a-long-and-secure-secret-key-that-you-should-change'),
    'ttl' => env('JWT_TTL', 60),
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
    'algo' => env('JWT_ALGO', 'HS256'),
    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],
    'persistent_claims' => [
        // 'foo',
        // 'bar',
    ],
    'lock_subject' => true,
    'leeway' => env('JWT_LEEWAY', 0),
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),
    'decrypt_cookies' => false,
    'providers' => [
        'jwt' => 'Tymon\JWTAuth\Providers\JWT\Lcobucci',
        'auth' => 'Tymon\JWTAuth\Providers\Auth\Illuminate',
        'storage' => 'Tymon\JWTAuth\Providers\Storage\Illuminate',
    ],
];
