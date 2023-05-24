<?php

namespace System\Router;

use ReflectionMethod;
use stdClass;
use System\Config\Config;

class Routing extends stdClass
{
    private $current_route;

    private $method_field;

    private $routes;

    private $values = [];

    private $compare = false;

    public function __construct()
    {
        $this->current_route = explode('/', trim(Config::get('app.CURRENT_ROUTE'), '/'));
        $this->method_field = $this->methodField();
        global $routes;
        $this->routes = $routes;
    }

    public function run()
    {
        $match = $this->matchMethod();
        if (empty($match)) {
            $this->error404();
        }

        $classPath = str_replace('\\', '/', $match['class']);
        $path = Config::get('app.BASE_DIR').'/app/Http/Controllers/'.$classPath.'.php';
        if (! file_exists($path)) {
            $this->error404();
        }

        $class = "\App\Http\Controllers\\".$match['class'];
        $object = new $class();
        if (method_exists($object, $match['method'])) {
            $reflection = new ReflectionMethod($class, $match['method']);
            $parameterCount = $reflection->getNumberOfParameters();
            if ($parameterCount <= count($this->values)) {
                call_user_func_array([$object, $match['method']], $this->values);
            } else {
                $this->error404();
            }
        } else {
            $this->error404();
        }
    }

    private function matchMethod()
    {
        $reservedRoutes = $this->routes[$this->method_field];

        foreach($reservedRoutes as $reservedRoute)
        {
            if($find = $this->find($reservedRoute)) return $find;

            $this->values = [];
        }

        return [];
    }

    public function find($reserve)
    {
        if ($this->compare($reserve['url']))
            return [
                'class' => $reserve['class'],
                'method' => $reserve['method']
            ];
    }

    private function compareRootPath($reservedRouteUrl)
    {
        if (! (trim($reservedRouteUrl, '/') === ''))
            return null;

        if(trim($this->current_route[0], '/') === '')
            $this->compare = true;
    }

    private function placementUrlParameters($reservedRouteUrl)
    {
        $reservedRouteUrlArray = explode('/', $reservedRouteUrl);

        if (count($this->current_route) !== count($reservedRouteUrlArray)) return;

        foreach ($this->current_route as $key => $currentRouteElement) {
            $reservedRouteUrlElement = $reservedRouteUrlArray[$key];
            if (
                substr($reservedRouteUrlElement, 0, 1) === '{'
                && substr($reservedRouteUrlElement, -1) === '}'
            )
                array_push($this->values, $currentRouteElement);
            elseif ($reservedRouteUrlElement !== $currentRouteElement)
                return;
        }

        $this->compare = true;
    }

    private function compare($reservedRouteUrl)
    {
        $this->compareRootPath($reservedRouteUrl);

        $this->placementUrlParameters($reservedRouteUrl);

        return $this->compare;
    }

    public function error404()
    {
        http_response_code(404);
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        $view404 = Config::get('app.ERRORS.404');
        if ($view404) {
            view($view404);
        } else {
            view('errors.404');
        }
        exit;
    }

    public function methodField()
    {
        $method_field = strtolower($_SERVER['REQUEST_METHOD']);

        if ($method_field == 'post' && isset($_POST['_method'])) {
            $methods = ['put', 'delete'];

            $method_field = (in_array($_POST['_method'], $methods))
            ? $_POST['_method']
            : $method_field;
        }

        return $method_field;
    }
}
