<?php

namespace DF\Routing;


use DF\Helpers\RouteScanner;

class Router extends AbstractRouter
{
    private $controller = "";
    private $action = "";
    public $routeParams = [];

    public $routeInfo = [];

    public function __construct() {

    }

    private function loadAllRoutes() {
        $scanner = new RouteScanner();

        $areasRoutes = $scanner->getAreasControllersRoutes("Areas", true);

        $baseControllersRoutes = $scanner->getAllControllersRoutes(true);

        $routes = array_merge($areasRoutes, $baseControllersRoutes);

        return $routes;
    }

    public function parseUrl() {
        $routes = $this->loadAllRoutes();

        $valid = false;

        foreach($routes as $routeInfo) {
            $routeString = $routeInfo['methodPattern'];

            preg_match("/\A$routeString\z/", $_GET['url'], $test);

            if(count($test) > 0) {
                $this->routeInfo = $routeInfo;
                $this->controller = $routeInfo['controller'];
                $this->action = $routeInfo['action'];
                $valid = true;

                if(count($test) > 1) {
                    for($i = 1; $i < count($routeInfo['parameters']) + 1; $i++) {
                        $this->routeParams[] = $test[$i];
                    }
                }

                break;
            }
        }

        if($valid == false) {
            throw new \Exception("Route not found");
        }
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }
}