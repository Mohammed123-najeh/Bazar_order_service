<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () {
    return 'Order Service is running!';
});

$router->post('/purchase/{itemNumber}', 'PurchaseController@purchase');

