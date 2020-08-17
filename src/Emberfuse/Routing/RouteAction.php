<?php

namespace Emberfuse\Routing;

class RouteAction
{
    public static function parse(string $action)
    {
        [$controller, $method] = explode('@', $action);

        return compact('controller', 'method');
    }
}
