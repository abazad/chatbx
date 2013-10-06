<?php
class Dmz_Controller {
    
    public static function dispatch() {
        $route = isset($_GET['route']) ? $_GET['route'] : 'index';
        $route = explode("/", $route);
        $controller = $route[0];
        $method = !empty($route[1]) ? $route[1] : "init";
        $params = !empty($route[2]) ? array_slice($route, 2) : NULL;
        $params = (count($params) == 1) ? $params[0] : $params;
        $path = dirname(dirname(__FILE__)).'/controllers/'.$controller.'.controller.php';
        try {
            if(file_exists($path)) {
                require_once $path;
                $class = new $controller;
                if(method_exists($class, $method)) {
                    $class->$method($params);
                } else {
                    throw new Exception("Method $method not exist.");
                }
            } else {
                throw new Exception("Controller $controller not exist.");
            }
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

}