<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 02.07.15
 * Time: 13:05
 */

class Router {

    private $method;
    private $module;
    private $params = array();

    public static $SUBFOLDER = 1;

    /**
     * Constructor. Parse request URI.
     * URI example: /module/method/param0/param1/.../paramN
     * @throws Exception
     */
    public function __construct()
    {
        $components = explode('/', $_SERVER['REQUEST_URI']);
        $componentsCount = sizeof($components);

        $subfolder = self::$SUBFOLDER;

        if ($componentsCount > 1) {
            $this->module = $components[$subfolder];
            $this->method = $components[$subfolder+1];

            $this->params = $_REQUEST;

            // Other parts of URI components are params
            for($i=$subfolder+2; $i<$componentsCount; $i++)
            {
                $this->params[$i-3] = $components[$i];
            }
        }else{
            throw new Exception("Can't init API core: bad request");
        }
    }

    /**
     * Returns method name
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns module name
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $name number or sting key
     * @param string $defaultValue Default value if key not exists
     * @param bool $required Required flag
     * @return mixed
     * @throws AppException
     */
    public function getParams($name, $defaultValue = "", $required = false)
    {
        if (!isset($this->params[$name])) {
            if ($required) {
                throw new AppException("Parameter {$name} is required");
            }
            return $defaultValue;
        }
        return $this->params[$name];
    }
}