<?php

$router->get('/', function () {
    return 'Order Service is running!';
});

// Before replication: only a single purchase endpoint
$router->post('/purchase/{itemNumber}', 'PurchaseController@purchase');
