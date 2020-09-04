<?php

namespace Emberfuse\Container;

use Closure;
use Exception;
use ArrayAccess;
use ReflectionClass;
use Psr\Container\ContainerInterface;
use Emberfuse\Support\DependencyResolver;
use Emberfuse\Container\Exceptions\BindingNotFoundException;
use Emberfuse\Container\Exceptions\BindingResolutionException;

class Container implements ContainerInterface, ArrayAccess
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * Registered bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Registered sharable instances of bindings.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    protected $buildStack = [];

    /**
     * The parameter override stack.
     *
     * @var array
     */
    protected $parameterOverride = [];

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
    public function isShared(string $abstract): bool
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
     * Register an existing instance as shared in the container.
     *
     * @param string                       $abstract
     * @param object|string|array|int|bool $instance
     *
     * @return object|string|array|int|bool
     */
    public function instance(string $abstract, $instance)
    {
        $this->instances[$abstract] = $instance;

        return $instance;
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
            $concrete = $this->makeClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Wrap given concrete binding type inside closure.
     *
     * @param string $abstract
     * @param string $concrete
     *
     * @return \Closure
     */
    protected function makeClosure(string $abstract, string $concrete): Closure
    {
        return function ($container, array $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete);
            }

            return $container->resolve($concrete, $parameters);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        try {
            return $this->resolve($id);
        } catch (BindingResolutionException $exception) {
            if ($this->has($id)) {
                throw $exception;
            }

            throw new BindingNotFoundException($id, $exception->getCode(), $exception);
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
    protected function resolve(string $abstract, array $parameters = [])
    {
        if (isset($this->instances[$abstract]) && empty($parameters)) {
            return $this->instances[$abstract];
        }

        $this->parameterOverride[] = $parameters;

        $concrete = $this->getConcrete($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }

        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        array_pop($this->parameterOverride);

        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param string $abstract
     *
     * @return \Closure|string
     */
    protected function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param mixed  $concrete
     * @param string $abstract
     *
     * @return bool
     */
    protected function isBuildable($concrete, string $abstract): bool
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * Instantiate a given concrete type with required dependencies.
     *
     * @param mixed $concrete
     *
     * @return mixed
     *
     * @throws \Emberfuse\Container\Exceptions\BindingResolutionException
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            $dependencies = count($this->parameterOverride)
                ? end($this->parameterOverride) : [];

            return call_user_func_array($concrete, [$this, $dependencies]);
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (Exception $e) {
            throw new BindingResolutionException("Class [$concrete] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return $reflector->newInstance();
        }

        $dependencies = $constructor->getParameters();

        $dependencies = $this->resolveDependencies($dependencies);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve given array of class dependencies.
     *
     * @param array $dependencies
     *
     * @return array
     *
     * @throws \Emberfuse\Container\Exceptions\BindingResolutionException
     */
    protected function resolveDependencies(array $dependencies): array
    {
        return (new DependencyResolver($this))->resolve(
            $dependencies,
            ...$this->parameterOverride
        );
    }

    /**
     * Throw a resolution exception that the concrete is not instantiable.
     *
     * @param string $concrete
     *
     * @return void
     *
     * @throws \Emberfuse\Container\Exceptions\BindingResolutionException
     */
    protected function notInstantiable(string $concrete): void
    {
        throw new BindingResolutionException("[$concrete] is not instantiable.");
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
     * Get the container's bindings.
     *
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Remove a resolved instance from the instance cache.
     *
     * @param string $abstract
     *
     * @return void
     */
    public function forgetInstance($abstract): void
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Clear all of the instances from the container.
     *
     * @return void
     */
    public function forgetInstances(): void
    {
        $this->instances = [];
    }

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param string $abstract
     *
     * @return void
     */
    protected function dropStaleInstances(string $abstract): void
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Determine if a given offset exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get the value at a given offset.
     *
     * @param string $key
     *
     * @return object|string|array|int|bool
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string                       $key
     * @param object|string|array|int|bool $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $value = $value instanceof Closure
            ? $value
            : function () use ($value) {
                return $value;
            };

        $this->bind($key, $value);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }

    /**
     * Dynamically access container services.
     *
     * @param string $key
     *
     * @return object|string|array|int|bool
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Dynamically set container services.
     *
     * @param string                       $key
     * @param object|string|array|int|bool $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}
