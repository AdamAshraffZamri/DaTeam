<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Check for maintenance mode
if (file_exists($maintenance = '/../DaTeamGit/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 2. Register the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// 3. Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());
