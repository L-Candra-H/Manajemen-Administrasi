<?php

return [
    'base_url' => isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] . '/manajemen_administrasi' : 'http://localhost/manajemen_administrasi',
    'db' => [
        'host' => 'localhost',
        'name' => 'manajemen_administrasi',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ]
];
