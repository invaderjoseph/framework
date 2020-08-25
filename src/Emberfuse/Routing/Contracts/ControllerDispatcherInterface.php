<?php

namespace Emberfuse\Routing\Contracts;

use Emberfuse\Routing\Route;

interface ControllerDispatcherInterface
{
    /**
     * Dispatch a request to a given controller and method.
     *
     * @param \Illuminate\Routing\Route $route
     * @param string                    $controller
     * @param string                    $method
     *
     * @return mixed
     */
    public function dispatch(Route $route, string $controller, string $method);
}
