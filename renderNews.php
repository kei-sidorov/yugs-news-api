<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 07.07.15
 * Time: 12:31
 */

/**
 * Output bad request
 *
 * @param $message
 */
function setBadRequest($message)
{
    header('HTTP/1.0 400 Bad Request', true, 400);

    echo "<h1>Bad request</h1>";
    echo "<p>" . $message . "</p>";

    exit();
}

require_once('config.php');

try {

    $itemId = (int)$_REQUEST['id'];

    if ($itemId == 0) {
        throw new AppException('Incorrect news item Id');
    }

    $config = parse_ini_file("./config/config.ini", true);
    $newsClass = $config["news"]["class"];
    $news = new $newsClass();

    $item = $news->get($itemId);

    $path = $config["global"]["path"];

    header('Content-Type: text/html; charset=utf-8');
    include('newsTemplate.php');

}catch (Exception $e) {
    setBadRequest($e->getMessage());
}