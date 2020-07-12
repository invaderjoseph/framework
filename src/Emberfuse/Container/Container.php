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
    private function isShared(string $abstract): bool
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
            return $this->make($id);
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
    private function resolve(string $abstract, array $parameters = [])
    {
        // Get concrete type from the container registry.
        $concrete = $this->getConcrete($abstract);

        // Determine if a sharable instance already exists within the container.
        if (isset($this->instances[$abstract]) && $parameters === []) {
            // If so, return the sharable instance.
            return $this->instances[$abstract];
        }

        // Otherwise the type required to be instantiated or called upon.
        $object = $this->build($concrete, $parameters);

        // Determine if the binding is set to be a singleton/sharable instance.
        if ($this->isShared($abstract)) {
            // If so, save the resolved instance in the sharable instances registry.
            $this->instances[$abstract] = $object;
        }

        // Return resolved results.
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
        // Determine if the given concrete type is callable.
        if ($concrete instanceof Closure) {
            // If so, execute the callable functions with appropriate parameters.
            return call_user_func_array($concrete, array_merge([$this], $parameters));
        }

        // If the given concrete type is not a callable, it means it is a string
        // that is a class name and has to be instantiated.
        try {
            // Make new reflection class object of the concrete type.
            // This is done so that the parameters/dependencies of the given class can
            // also be resolved recursively.
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $exception) {
            // If an exception was caught it means the concrete type is not a class name
            // and cannot be resolved.
            $this->throwBindingResolutionException($concrete, $exception);
        }

        // Determine if the class is not instantiable.
        if (!$reflector->isInstantiable()) {
            // If so, throw a resolution exception.
            return $this->throwBindingResolutionException(
                "Target [$concrete] is not instantiable."
            );
        }

        // Get the constructor method of the class.
        $constructor = $reflector->getConstructor();

        // Determine if the class has a constructor method.
        if (is_null($constructor)) {
            // If not, it means the class has no dependencies and can be
            // instantiated immediately.
            return new $concrete();
        }

        // Get all parameters from the class constructor.
        $dependencies = $constructor->getParameters();

        try {
            // Resolve all dependencies to it's relevant types.
            $dependencies = $this->resolveDependencies(
                array_merge($dependencies, $parameters)
            );
        } catch (BindingResolution $exception) {
            throw $exception;
        }

        // return a new instance of the now resolving class with all appropriate
        // dependencies (resolved dependencies).
        return $reflector->newInstanceArgs($dependencies);
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

        // Iterate through each set of parameters.
        foreach ($dependencies as $dependency) {
            // Determine and resolve each type of the parameters.
            $results[] = is_null($dependency->getClass())
                // Primitive types include, "string", "array", "integer" and "boolean".
                ? $this->resolvePrimitive($dependency)
                // These are parameters with class names.
                : $this->resolveClass($dependency);
        }

        // Return resolved results.
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
        // Determine if the parameters have default values assigned to them.
        if ($parameter->isDefaultValueAvailable()) {
            // If so, return the default values.
            return $parameter->getDefaultValue();
        }

        // Else throw primitive is unresolvable error.
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
        // Recursively resolve the given object type.
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
     * Drop all of the stale instances and aliases.
     *
     * @param string $abstract
     *
     * @return void
     */
    private function dropStaleInstances(string $abstract): void
    {
        // Remove already existing instances of given binding.
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }
}
