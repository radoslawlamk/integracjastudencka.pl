<?php

return [
    'app_name' => 'Integracja Studencka',
    'base_url' => getenv('APP_URL') ?: '',
    'timezone' => 'Europe/Warsaw',
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'database' => getenv('DB_DATABASE') ?: 'integracja_studencka',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
];
