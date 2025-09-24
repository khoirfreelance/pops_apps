<?php

/* return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'], // alamat frontend Vue
    'allowed_headers' => ['*'], // supaya Authorization ikut
    'exposed_headers' => ['*'],
    'supports_credentials' => true, // kalau Bearer token, false
]; */

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173', // ganti sesuai origin Vue lu
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];

