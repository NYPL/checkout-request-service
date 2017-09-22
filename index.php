<?php
require __DIR__ . '/vendor/autoload.php';

use NYPL\Services\Controller\CheckoutRequestController;
use NYPL\Services\ServiceContainer;
use NYPL\Services\Swagger;
use NYPL\Starter\Service;
use NYPL\Starter\Config;
use NYPL\Starter\ErrorHandler;

try {
    Config::initialize(__DIR__);

    $container = new ServiceContainer();

    $service = new Service($container);

    $service->get('/docs/checkout-requests', Swagger::class);

    $service->post('/api/v0.1/checkout-requests', CheckoutRequestController::class . ':processCheckOutRequest');

    $service->run();
} catch (Exception $exception) {
    ErrorHandler::processShutdownError($exception->getMessage(), $exception);
}
