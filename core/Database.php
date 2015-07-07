<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 02.07.15
 * Time: 13:39
 */

class Database {

    public $_db;
    static $_instance;

    private function __construct($host, $username, $password, $database)
    {
        try {
            $this->_db = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
        }catch(PDOException $Exception) {
            throw new AppException($Exception->getMessage(), (int) $Exception->getCode());
        }

        $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_db->query("SET NAMES 'utf8'");
    }

    private function __clone()
    {
    }

    /**
     * @return Database
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self))
        {
            $config = parse_ini_file("./config/config.ini", true);
            $config = $config["database"];
            self::$_instance = new self($config["host"], $config["username"], $config["password"], $config["database"]);
        }

        return self::$_instance;
    }

}