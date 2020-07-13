<?php

declare(strict_types=1);

namespace Emberfuse\Container;

use Closure;
use Exception;
use ArrayAccess;
use Psr\Container\ContainerInterface;
use Emberfuse\Container\Exceptions\BindingNotFound;
use Emberfuse\Container\Exceptions\BindingResolution;

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
        // Determine if the given binding has already been registered to the container.
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
        // Determine if the given binding is set to be a sharable/singleton instance.
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
        // Bind a sharable instance of a binding to the service container.
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
        // Save given instance of class to sharable instances registry.
        $this->instances[$abstract] = $instance;

        // Return same instance of class.
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
        // Determine if a binsing with same abstract type exists.
        if ($this->has($abstract)) {
            // If so, remove it from the register and make a fresh registration.
            $this->dropStaleInstances($abstract);
        }

        // Determine if the concrete type is provided.
        if (is_null($concrete)) {
            // If it is not, assign the abstract type to the concrete.
            // This is usually because the abstract is a name of a class
            // and the class is expected to be automatically resolved
            // during the binding process.
            $concrete = $abstract;
        }

        // Determine if the provided concrete type is a callable
        if (!$concrete instanceof Closure) {
            // If not, it is probably because the concrete is set to be the same
            // as the abstract type and so it needs to be resolved and wrapped inside
            // a callable function.
            $concrete = $this->makeClosure($concrete);
        }

        // Bind the abstract and concrete types into the container registry as
        // one of the container's bindings.
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
        // Use the container as an argument so the given concrete type which is
        // usually a class name can be resolved during the "make/resolve" process.
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
            // Try to resolve the given binding from the container.
            return $this->resolve($id);
        } catch (BindingResolution $exception) {
            // If an exception was thrown it is either because the binding does
            // not exist within the container or an error occurred during the resolution
            // process of the given binding.
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
        // Use the "resolve" method to resolve the given binding from the container
        // or instantly resolve the class type for usage,
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
        // Determine if a concrete type is bound in the container.
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        // If no concrete type was found, the given abstract type was not bound
        // inside the container, so return the given string as the concrete type to be
        // resolved as a class.
        return $abstract;
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return \Psr\Container\ContainerInterface|static
     */
    public static function getInstance()
    {
        // Determine if a globally available instance of the
        // service container is available to access.
        if (is_null(static::$instance)) {
            // If not, make new instance and set self as instance.
            static::makeInstance(new static());
        }

        // Return globally available instance of the container.
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
        // Set default sharable insensate of the service container.
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
        // Remove already existing instances of given binding.
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
