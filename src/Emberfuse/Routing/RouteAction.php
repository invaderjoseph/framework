<?php

namespace Emberfuse\Routing;

use InvalidArgumentException;

class RouteAction
{
    /**
     * Parse route action string and separate into class and method.
     *
     * @param string $action
     *
     * @return array
     */
    public static function parse(string $action): array
    {
        if (strpos($action, '@') === false) {
            throw new InvalidArgumentException('Route action is invalid.');
        }

        [$controller, $method] = explode('@', $action);

        return compact('controller', 'method');
    }
}
