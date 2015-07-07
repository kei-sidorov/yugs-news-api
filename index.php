<?php

require_once('config.php');

$config = parse_ini_file("./config.ini", true);
Router::$SUBFOLDER = $config["global"]["app-level"];

try {
    $router = new Router();
}catch (AppException $e) {
    setBadRequest($e->getMessage());
}

try {
    switch ($router->getModule()) {
        case 'news': {
            $newsClass = $config["news"]["class"];
            $news = new $newsClass();

            switch ($router->getMethod())
            {
                case 'delete':
                {
                    $id = $router->getParams("id");
                    $result = $news->delete($id);

                    if ($result) {
                        setResult(true, $router);
                    }
                    break;
                }

                case 'get':
                {
                    $id = $router->getParams("id", 0, true);
                    $result = $news->get($id);

                    if ($result) {
                        setResult(true, $router, $result);
                    }
                    break;
                }

                case 'getList':
                {
                    $type = $router->getParams("type", 0);
                    $page = $router->getParams("page", 0);
                    $limit = $router->getParams("limit", 20);

                    $result = $news->getList($type, $limit, $page);

                    setResult(true, $router, $result);

                    break;
                }

                case 'add':
                {
                    $type = $router->getParams("type", 0, true);
                    $header = $router->getParams("header", 0, true);
                    $text = $router->getParams("text", 0, true);
                    $images = $router->getParams("images", 0, true);
                    $date = $router->getParams("date", 0);
                    $notify = $router->getParams("notify", 0);
                    $clean = $router->getParams("clean", 0);

                    $images = json_decode($images, true);

                    $result = $news->add($type, $header, $text, $images, $date);

                    if ($notify == 1)
                    {
                        $notifyController = new Notify();
                        $notifyController->sendData($header, $result);
                    }

                    setResult(true, $router, $result);

                    break;
                }

                case 'render':
                {
                    $id = $router->getParams("id", 0, true);
                    $path = $config["global"]["path"];
                    echo file_get_contents("http://" . $_SERVER["HTTP_HOST"] . $path . "renderNews.php?id=" . $id);
                    exit();
                }

                default:
                {
                    setBadRequest('Unknown method for module news');
                }
            }
            break;
        }

        case 'push':
        {
            $notifyController = new Notify();

            switch ($router->getMethod())
            {
                case 'register_token_android':
                {
                    $token = $router->getParams("token", 0, true);
                    $notifyController->registerNewToken($token, Notify::TOKEN_TYPE_GCM);
                    setResult(true, $router);
                    break;
                }

                case 'register_token_ios':
                {
                    $token = $router->getParams("token", 0, true);
                    $notifyController->registerNewToken($token, Notify::TOKEN_TYPE_APS);
                    setResult(true, $router);
                    break;
                }

                default:
                {
                    setBadRequest('Unknown method for module push');
                }
            }

            break;
        }

        default:
        {
            setBadRequest('Unknown module' . $router->getModule());
        }
    }
}catch (APIException $e) {
    setResult(false, $router, array("error_message" => $e->getMessage(),
                                    "error_code" => $e->getCode())
             );
}catch (AppException $e) {
    setBadRequest($e->getMessage());
}

/**
 * Output API result
 *
 * @param bool $success Is success flag
 * @param Router $router Current instance of Router
 * @param string|array $data Output data
 */
function setResult($success, Router $router, $data = "")
{
    header( "Content-Type: application/json" );
    echo json_encode( array(
        "module" => $router->getModule(),
        "method" => $router->getMethod(),
        "success" => (bool) $success,
        "data" => $data
    ) );
}

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
