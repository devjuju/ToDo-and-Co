<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists(\Symfony\Component\Dotenv\Dotenv::class)) {
    (new Symfony\Component\Dotenv\Dotenv())
        ->bootEnv(dirname(__DIR__) . '/.env');
}

$kernel = new Kernel(
    $_SERVER['APP_ENV'] ?? 'dev',
    (bool) ($_SERVER['APP_DEBUG'] ?? true)
);

$request = Request::createFromGlobals();

$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
