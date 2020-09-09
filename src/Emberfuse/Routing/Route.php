<?php

namespace Emberfuse\Routing;

use LogicException;
use ReflectionMethod;
use RuntimeException;
use BadMethodCallException;
use Emberfuse\Container\Container;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Routing\Validators\UriValidator;
use Symfony\Component\HttpFoundation\Response;
use Emberfuse\Routing\Validators\HostValidator;
use Emberfuse\Routing\Contracts\RouterInterface;
use Emberfuse\Routing\Validators\MethodValidator;

class Route
{
    /**
     * HTTP method type,.
     *
     * @var string
     */
    public $method;

    /**
     * URI string.
     *
     * @var string
     */
    public $uri;

    /**
     * Route response action.
     *
     * @var string
     */
    protected $action;

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * The compiled version of the route.
     *
     * @var \Symfony\Component\Routing\CompiledRoute
     */
    protected $compiled;

    /**
     * Instance of Emberfuse router.
     *
     * @var \Emberfuse\Routing\Contracts\RouterInterface
     */
    protected $router;

    /**
     * Instance of Emberfuse container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The validators used by the routes.
     *
     * @var array
     */
    public static $validators = [
        MethodValidator::class,
        HostValidator::class,
        UriValidator::class,
    ];

    /**
     * Create new instance of Emberfuse route.
     *
     * @param string $method
     * @param string $uri
     * @param string $action
     *
     * @return void
     */
    public function __construct(string $method, string $uri, string $action)
    {
        $this->method = $method;
        $this->uri = $this->prefixUri($uri);
        $this->action = $this->parseAction($action);
    }

    /**
     * Run the route action and return the response.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     */
    public function run(Request $request)
    {
        $this->container = $this->container ?: new Container();

        return $this->runController($request);
    }

    /**
     * Run the route action and return the response.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     */
    protected function runController(Request $request)
    {
        $instance = $this->container->make($this->action['controller']);

        if (! method_exists($instance, $method = $this->action['method'])) {
            throw new BadMethodCallException("Method named [$method] does not exist on controller.");
        }

        $parameters = $this->container->resolveDependencies(
            (new ReflectionMethod($instance, $method))->getParameters(),
            $this->parametersWithoutNulls()
        );

        return $instance->callAction($method, $parameters);
    }

    /**
     * Determine if the route matches given request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function matches(Request $request): bool
    {
        if (is_null($this->compiled)) {
            $this->compile();
        }

        foreach ($this->getValidators() as $validator) {
            $validator = new $validator();

            if (! $validator->validate($this, $request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Bind the route to a given request for execution.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Emberfuse\Routing\Route
     */
    public function bind(Request $request): Route
    {
        $this->bindParameters($request);

        return $this;
    }

    /**
     * Extract the parameter list from the request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function bindParameters(Request $request): void
    {
        $this->parameters = (new RouteParameters($this))->bind($request);
    }

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function parametersWithoutNulls(): array
    {
        return array_filter($this->parameters(), function ($parameter) {
            return ! is_null($parameter);
        });
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function parameters(): array
    {
        if (isset($this->parameters)) {
            return $this->parameters;
        }

        throw new LogicException('Route is not bound.');
    }

    /**
     * Prefix the given URI with the last prefix.
     *
     * @param string $uri
     *
     * @return string
     */
    public function prefixUri(string $uri): string
    {
        return trim('/' . trim($uri, '/'), '/') ?: '/';
    }

    /**
     * Parse given action string to controller and method.
     *
     * @param string $action
     *
     * @return array
     */
    protected function parseAction(string $action): array
    {
        return RouteAction::parse($action);
    }

    /**
     * Get route URI.
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get route HTTP method.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get route HTTP method.
     *
     * @return array
     */
    public function wheres(): array
    {
        return $this->wheres;
    }

    /**
     * Get route's response action.
     *
     * @return array
     */
    public function getAction(): array
    {
        return $this->action;
    }

    /**
     * Get route's response action.
     *
     * @return \Symfony\Component\Routing\CompiledRoute|null
     */
    public function getCompiled(): ?CompiledRoute
    {
        return $this->compiled;
    }

    /**
     * Set a regular expression requirement on the route.
     *
     * @param string $name
     * @param string $expression
     *
     * @return \Emberfuse\Routing\Route
     */
    public function setWhereClouse(string $name, string $expression): Route
    {
        $this->wheres[$name] = $expression;

        return $this;
    }

    /**
     * Set instance of router.
     *
     * @param \Emberfuse\Routing\Contracts\RouterInterface $router
     *
     * @return \Emberfuse\Routing\Route
     */
    public function setRouter(RouterInterface $router): Route
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Set service container instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Emberfuse\Routing\Route
     */
    public function setContainer(ContainerInterface $container): Route
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Compile given route instance.
     *
     * @return \Emberfuse\Routing\Route
     */
    public function compile(): Route
    {
        $this->compiled = RouteCompiler::compile($this);

        return $this;
    }

    /**
     * Get all route validator classes.
     *
     * @return array
     */
    protected function getValidators(): array
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }

        throw new RuntimeException('No route validators found.');
    }
}
