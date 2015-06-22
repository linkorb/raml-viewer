<?php

// use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../app/bootstrap.php';
// $app->run(Request::createFromGlobals());
$app->run();
