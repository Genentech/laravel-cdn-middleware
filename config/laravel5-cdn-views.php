<?php

return [
    'cdn_url' => 'https://cdn.com',
    'enabled' => env('CDN_ENABLED', false),
    'ssl_enabled' => false,
    'tags' => [
        'script',
        'img',
        'link'
    ],
    'disabled_routes' => [
        'contact-us',
        'contact-us/*',
    ],
];
