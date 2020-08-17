<?php

namespace Emberfuse\Routing;

use BadMethodCallException;

class ControllerDispatcher implements ControllerDispatcherInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch a request to a given controller and method.
     *
     * @param \Illuminate\Routing\Route $route
     * @param string                    $controller
     * @param string                    $method
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function dispatch(Route $route, string $controller, string $method)
    {
        $controller = $this->container->make($controller);

        if (method_exists($controller, $method)) {
            return call_user_func_array(
                [$controller, $method],
                [$route->parameters()]
            );
        }

        throw new BadMethodCallException();
    }
}
