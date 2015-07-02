<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 02.07.15
 * Time: 13:45
 */

class AppException extends Exception {

    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->storeException();
    }

    private function storeException()
    {
        $config = parse_ini_file("config.ini", true);
        $exceptionsPath = $config["errors"]["exceptions-path"];

        $fileName = $exceptionsPath . date("d-m-Y_His") . ".txt";
        $data = print_r($_REQUEST, true) . "\n" . $this->getMessage() . "\n" . $this->getTraceAsString();
        file_put_contents($fileName, $data);
    }


}