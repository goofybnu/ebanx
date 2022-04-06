<?php

use App\Controllers\EbanxController;
use App\Handlers\ErrorHandler;
use App\Handlers\NotAllowedHandler;
use App\Handlers\NotFoundHandler;
use Illuminate\Database\Capsule\Manager;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

$App = new App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'sqlite',
            'database' => realpath(__DIR__ . '/ebanx.sqlite'),
            'prefix' => ''
        ]
    ]
]);

$AppContainer = $App->getContainer();

$AppContainer['notFoundHandler'] = function ($AppContainer) {
    return new NotFoundHandler;
};

$AppContainer['notAllowedHandler'] = function ($AppContainer) {
    return new NotAllowedHandler;
};

$AppContainer['phpErrorHandler'] = function ($AppContainer) {
    return new ErrorHandler;
};

$AppContainer['errorHandler'] = function ($AppContainer) {
    return new ErrorHandler;
};

$Capsule = new Manager;
$Capsule->addConnection($AppContainer['settings']['db']);
$Capsule->setAsGlobal();
$Capsule->bootEloquent();

$AppContainer['db'] = function ($AppContainer) use ($Capsule) {
    return $Capsule;
};

$AppContainer['EbanxController'] = function ($AppContainer) {
    return new EbanxController($AppContainer);
};

require_once __DIR__ . '/routes.php';

$App->run();
