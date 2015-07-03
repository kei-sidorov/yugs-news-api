<?php

require_once('AppException.php');
require_once('Database.php');
require_once('Notify.php');
require_once('Router.php');

try {
    $router = new Router();
}catch (AppException $e) {
    header('HTTP/1.0 400 Bad Request', true, 400);
    exit();
}

echo $router->getModule();