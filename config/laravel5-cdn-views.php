<?php

return [
    'cdn_url' => 'http://cdn.gene.com',
    'enabled' => true,
    'blade_enabled' => true,
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
