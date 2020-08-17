<?php

namespace Emberfuse\Routing;

use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RouteCompiler;
use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Routing\Validators\UriValidator;
use Emberfuse\Routing\Validators\HostValidator;
use Emberfuse\Routing\Contracts\RouterInterface;
use Emberfuse\Routing\Validators\MethodValidator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Route
{
    protected $uri;

    protected $method;

    protected $action;

    protected $compiled;

    protected $router;

    protected $container;

    protected static $validators = [
        UriValidator::class,
        MethodValidator::class,
        HostValidator::class,
    ];

    public function __construct(string $uri, string $method, string $action)
    {
        $this->uri = $this->prefix($uri);
        $this->method = $method;
        $this->action = RouteAction::parse($action);
    }

    public function run()
    {
        $this->container();

        try {
            return $this->runController();
        } catch (HttpException $e) {
            return $e->getResponse();
        }
    }

    protected function runController()
    {
        return $this->dispatch($this, $this->getController(), $this->getControllerMethod());
    }

    public function getController(): string
    {
        return $this->action['controller'];
    }

    protected function getControllerMethod(): string
    {
        return $this->action['method'];
    }

    public function matches(Request $request)
    {
        $this->compileRoute();

        foreach (static::validators() as $validator) {
            $validator = $this->container->make($validator);

            if (!$validator->validate($this, $request)) {
                return false;
            }
        }

        return true;
    }

    protected function compileRoute()
    {
        dd(RouteCompiler::compile($this));

        if (!$this->compiled) {
            $this->compiled = RouteCompiler::compile($this);
        }

        return $this->compiled;
    }

    protected function prefix(string $uri): string
    {
        return trim('/' . trim($uri, '/'), '/') ?: '/';
    }

    public function method()
    {
        return $this->method;
    }

    public function uri()
    {
        return $this->uri;
    }

    public function action()
    {
        return $this->action;
    }

    public function getCompiled()
    {
        return $this->compiled;
    }

    public static function validators()
    {
        return static::$validators;
    }

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;

        return $this;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
