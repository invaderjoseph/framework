<?php

declare(strict_types=1);

namespace Emberfuse\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Emberfuse\Container\Exceptions\BindingNotFound;
use Emberfuse\Container\Exceptions\BindingResolution;

final class Container implements ContainerInterface
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    private static $instance;

    /**
     * Registered bindings.
     *
     * @var array
     */
    private $bindings = [];

    /**
     * Registered sharable instances of bindings.
     *
     * @var array
     */
    private $instances = [];

    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    private $buildStack = [];

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    /**
     *  Determine if the bound type is aset as sharable.
     *
     * @param string $abstract
     *
     * @return bool
     */
    private function isShared(string $abstract): bool
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['shared']) &&
            true === $this->bindings[$abstract]['shared']);
    }

    /**
     * Register a shared binding in the container.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register given binding with the container.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     * @param bool                 $shared
     *
     * @return void
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if ($this->has($abstract)) {
            $this->dropStaleInstances($abstract);
        }

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            $concrete = $this->makeClosure($concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Wrap given concrete binding type inside closure.
     *
     * @param string $concrete
     *
     * @return \Closure
     */
    protected function makeClosure(string $concrete): Closure
    {
        return function ($container) use ($concrete) {
            return $container->build($concrete);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        try {
            return $this->make($id);
        } catch (BindingResolution $exception) {
            if ($this->has($id)) {
                throw $exception;
            }

            throw new BindingNotFound($id, $exception->getCode(), $exception);
        }
    }

    /**
     * Resolve the requested binding from the container.
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return \Object|string|int|array|bool
     *
     * @throws \Emberfuse\Container\Exception\BindingResolution
     */
    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Resolve the requested binding from the container.
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return \Object|string|int|array|bool
     *
     * @throws \Emberfuse\Container\Exception\BindingResolution
     */
    private function resolve(string $abstract, array $parameters = [])
    {
        $concrete = $this->getConcrete($abstract);

        if (isset($this->instances[$abstract]) && $parameters === []) {
            return $this->instances[$abstract];
        }

        $object = $this->build($concrete, $parameters);

        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param string $abstract
     *
     * @return \Closure|string
     */
    private function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Call the callback of the given binding with an array of parameters if given any.
     *
     * @param \Closure\string $concrete
     * @param array[]         $parameters
     *
     * @return \Object|string|int|array|bool
     *
     * @throws \Emberfuse\Container\Exception\BindingResolution
     */
    private function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return call_user_func_array($concrete, [$this, ...$parameters]);
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $exception) {
            $this->throwBindingResolutionException($concrete, $exception);
        }

        if (!$reflector->isInstantiable()) {
            return $this->throwBindingResolutionException(
                "Target [$concrete] is not instantiable."
            );
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete();
        }

        $dependencies = $constructor->getParameters();

        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (BindingResolution $exception) {
            throw $exception;
        }

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve given array of dependencies.
     *
     * @param array $dependencies
     *
     * @return \Object|string|int|array|bool
     *
     * @throws \Emberfuse\Container\Exception\BindingResolution
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $results[] = is_null($dependency->getClass())
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);
        }

        return $results;
    }

    /**
     * Resolve primitive type dependencies.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return string|int|array|bool
     *
     * @throws \Emberfuse\Container\Exception\BindingResolution
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->throwBindingResolutionException(
            "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}"
        );
    }

    /**
     * Resolve dependency of type object.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return object
     *
     * @throws \Emberfuse\Container\Exception\BindingResolution
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        return $this->make($parameter->getClass()->name);
    }

    /**
     * Throw new instance of BindingResolution exception.
     *
     * @param string|null     $abstract
     * @param \Exception|null $exception
     *
     * @return void
     *
     * @throws \Emberfuse\Container\Exception\BindingResolution
     */
    protected function throwBindingResolutionException(?string $abstract = null, ?Exception $exception = null): void
    {
        throw new BindingResolution($abstract, $exception->getCode(), $exception);
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return \Psr\Container\ContainerInterface|static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::makeInstance(new static());
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param \Psr\Container\ContainerInterface|null $container
     *
     * @return \Psr\Container\ContainerInterface|null
     */
    public static function makeInstance(?ContainerInterface $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param string $abstract
     *
     * @return void
     */
    private function dropStaleInstances(string $abstract): void
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }
}
