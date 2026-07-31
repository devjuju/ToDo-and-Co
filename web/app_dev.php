<?php

use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/app/AppKernel.php';

$kernel = new AppKernel('dev', true);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
