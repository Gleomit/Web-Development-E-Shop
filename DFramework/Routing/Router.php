<?php
namespace DF\Routing;

use DF\Helpers\RouteScanner;
use DF\Services\RouteService;

class Router extends AbstractRouter
{
    private $controller = "";
    private $action = "";

    public $routeParams = [];

    public $routeInfo = [];

    public function __construct()
    {

    }

    public function parseUrl()
    {
        $routes = $this->loadAllRoutes();

        $valid = false;

        $requestUrl = $_GET['url'];

        if($requestUrl[strlen($requestUrl) - 1] === '/' && substr_count($requestUrl, '/') > 1) {
            $requestUrl = substr($requestUrl, 0, strlen($requestUrl) - 1);
        }

        foreach($routes as $routeInfo) {
            $routeString = $routeInfo['methodPattern'];

            preg_match("/\A$routeString\z/", $requestUrl, $test);

            if(count($test) > 0 && $_SERVER['REQUEST_METHOD'] === $routeInfo['requestType']) {
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

        if($valid === false) {
            RouteService::redirect('home', '404', [], true);
        }
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    private function loadAllRoutes()
    {
        $scanner = new RouteScanner();

        $areasRoutes = $scanner->getAreasControllersRoutes("Areas", true);

        $baseControllersRoutes = $scanner->getAllControllersRoutes(true);

        $routes = array_merge($areasRoutes, $baseControllersRoutes);

        return $routes;
    }
}