<?php

namespace Emberfuse\Routing;

use Symfony\Component\HttpFoundation\Request;

class RouteParameters
{
    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    protected $parameterNames;

    /**
     * Instance of Emberfuse route.
     *
     * @var \Emberfuse\Routing\Route
     */
    protected $route;

    /**
     * Create new instance of route parameter binder.
     *
     * @param \Emberfuse\Routing\Route $route
     *
     * @return void
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Extract the parameter list from the request.
     *
     * @param \Emberfuse\Routing\Route                  $route
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function bind(Request $request): array
    {
        $this->compileParameterNames($this->route);

        return $this->matchToRouteKeys(
            array_slice($this->bindPathParameters($request), 1)
        );
    }

    /**
     * Combine a set of parameter matches with the route's keys.
     *
     * @param array $matches
     *
     * @return array
     */
    protected function matchToRouteKeys(array $matches): array
    {
        if (count($this->parameterNames) == 0) {
            return [];
        }

        $parameters = array_intersect_key(
            $matches,
            array_flip($this->parameterNames)
        );

        return array_filter($parameters, function ($value) {
            return is_string($value) && strlen($value) > 0;
        });
    }

    /**
     * Get the parameter names for the route.
     *
     * @return void
     */
    protected function compileParameterNames(): void
    {
        preg_match_all('/\{(.*?)\}/', $this->route->uri, $matches);

        $this->parameterNames = array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);
    }

    /**
     * Get the parameter matches for the path portion of the URI.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function bindPathParameters(Request $request): array
    {
        preg_match(
            $this->route->getCompiled()->getRegex(),
            '/' . rawurldecode(trim($request->getPathInfo(), '/')),
            $matches
        );

        return $matches;
    }
}
