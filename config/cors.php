<?php

return [
    // Include Sanctum and API routes
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Allow all methods
    'allowed_methods' => ['*'],

    // Explicitly allow your dev origins (no wildcard when credentials are used)
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    // Not using patterns here
    'allowed_origins_patterns' => [],

    // Allow all headers
    'allowed_headers' => ['*'],

    // No custom exposed headers
    'exposed_headers' => [],

    // No cache
    'max_age' => 0,

    // CRITICAL: must be true for cookies (withCredentials) to work
    'supports_credentials' => true,
];
