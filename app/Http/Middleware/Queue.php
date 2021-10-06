<?php

namespace App\Http\Middleware;

class Queue {
    private static $default = [];
    private static $map = [];

    private $middlewares = [];

    private $controller;

    private $controllerArgs = [];

    public function __construct($middlewares, $controller, $action, $controllerArgs) {
        //$this->middlewares = $middlewares;
        $this->middlewares = array_merge(self::$default, $middlewares);
        $this->controller = $controller;
        $this->action = $action;
        $this->controllerArgs = $controllerArgs;
    }

    public function next($request, $response) {
        if (empty($this->middlewares)) {
            $controller = new $this->controller();
            $controller->{$this->action}($request, $response);
            return call_user_func_array([$this->controller, $this->action], [$request, $response]);
        }

        $middleware = array_shift($this->middlewares);

        if (!isset(self::$map[$middleware])) {
            return $response->status(500)->send("problema ao processar");
        }

        $queue = $this;
        $next = function($request, $response) use($queue) {
            return $queue->next($request, $response);
        };

        return (new self::$map[$middleware])->handle($request, $response, $next);

    }

    public static function setMap($map) {
        self::$map = $map;
    }

    public static function setDefault($default) {
        self::$default = $default;
    }
}