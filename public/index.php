<?php
set_exception_handler(function($e) {
    echo '<pre style="background:#1a1a1a;color:#ff6b6b;padding:20px;font-size:13px;">';
    echo "<b>" . get_class($e) . "</b>: " . $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo '</pre>';
    exit;
});
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
