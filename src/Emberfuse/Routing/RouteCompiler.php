<?php

namespace Emberfuse\Routing;

use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Route as SymfonyRoute;

class RouteCompiler
{
    /**
     * Compile the route into a Symfony CompiledRoute instance.
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public static function compile(Route $route): CompiledRoute
    {
        return static::createSymfonyRoute($route)->compile();
    }

    /**
     * Create Symfony route instance.
     *
     * @param \Emberfuse\Routing\Route $route
     *
     * @return \Symfony\Component\Routing\Route
     */
    public static function createSymfonyRoute(Route $route): SymfonyRoute
    {
        $routeUri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $route->uri());

        return new SymfonyRoute(
            $routeUri,
            (new self())->extractOptionalParameters($route),
            $route->wheres(),
            ['utf8' => true, 'action' => $route->getAction()],
            '',
            [],
            [$route->method]
        );
    }

    /**
     * Get the optional parameters for the route.
     *
     * @param \Emberfuse\Routing\Route $route
     *
     * @return array
     */
    protected function extractOptionalParameters(Route $route): array
    {
        preg_match_all('/\{(\w+?)\?\}/', $route->uri, $matches);

        return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
    }
}
