<?php

namespace Emberfuse\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCompiler as SymfonyRouteCompiler;

class RouteCompiler extends SymfonyRouteCompiler
{
    public static function compile(Route $route)
    {
        return $this->makeSymfonyRoute($route)->compile();
    }

    protected function makeSymfonyRoute(Route $route)
    {
        return new SymfonyRoute($route);
    }
}
