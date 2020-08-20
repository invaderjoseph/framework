<?php

namespace Emberfuse\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

class RouteCompiler
{
    public static function compile(Route $route)
    {
        return (new SymfonyRoute(
            $route->uri(),
            $route->optionalParameters
        ))->compile();
    }
}
