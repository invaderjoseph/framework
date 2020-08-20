<?php

namespace Emberfuse\Routing;

class RouteParameters
{
    protected $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function bind()
    {
        $this->compileParameterNames()->compileOptionalParameters();
    }

    protected function compileParameterNames()
    {
        preg_match_all('/\{(.*?)\}/', $this->route->uri(), $matches);

        $this->route->parameterNames = array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);

        return $this;
    }

    protected function compileOptionalParameters()
    {
        preg_match_all('/\{(\w+?)\?\}/', $this->route->uri(), $matches);

        $this->route->optionalParameters = isset($matches[1])
            ? array_fill_keys($matches[1], null)
            : [];

        return $this;
    }
}
