<?php

return [
    // Allow API routes and Sanctum's CSRF cookie endpoint
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Allow all HTTP methods from the frontend during development
    'allowed_methods' => ['*'],

    // Explicitly allow Vite dev server origins
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    // Allow all headers
    'allowed_headers' => ['*'],

    // No custom exposed headers
    'exposed_headers' => [],

    // No cache for preflight
    'max_age' => 0,

    // Required when using cookies (Sanctum) from the browser
    'supports_credentials' => true,
];
