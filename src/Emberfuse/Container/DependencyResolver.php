<?php

namespace Emberfuse\Container;

use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Emberfuse\Container\Exceptions\DependencyResolutionException;

class DependencyResolver
{
    /**
     * Instance of service container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The parameter override stack.
     *
     * @var array
     */
    protected $parameterOverride = [];

    /**
     * Create new instance of Method Dependency Resolver.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve given method/class dependencies.
     *
     * @param array $dependencies
     *
     * @return array
     *
     * @throws \Emberfuse\Container\Exceptions\DependencyResolutionException
     */
    public function resolve(array $dependencies): array
    {
        $resolved = [];

        foreach ($dependencies as $dependency) {
            if ($this->hasParameterOverride($dependency)) {
                $resolved[] = $this->getParameterOverride($dependency);

                continue;
            }

            $resolution = is_null($dependency->getClass())
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);

            $resolved[] = $resolution;
        }

        return $resolved;
    }

    /**
     * Resolve a dependency that has a type of primitive.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws \Emberfuse\Container\Exceptions\DependencyResolutionException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new DependencyResolutionException("[$parameter] is unresolvable.");
    }

    /**
     * Resolve a class based dependency.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return object
     *
     * @throws \Emberfuse\Container\Exceptions\DependencyResolutionException
     */
    protected function resolveClass(ReflectionParameter $parameter): object
    {
        try {
            return $this->container->make($parameter->getClass()->name);
        } catch (DependencyResolutionException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * Set parameters to override given dependencies.
     *
     * @param array $parameters
     *
     * @return \Emberfuse\Container\DependencyResolver
     */
    public function setParameterOverride(array $parameters = []): DependencyResolver
    {
        $this->parameterOverride[] = $parameters;

        return $this;
    }

    /**
     * Determine if the given dependency has a parameter override.
     *
     * @param \ReflectionParameter $dependency
     *
     * @return bool
     */
    protected function hasParameterOverride(ReflectionParameter $dependency): bool
    {
        return array_key_exists(
            $dependency->name,
            $this->getLastParameterOverride()
        );
    }

    /**
     * Get a parameter override for a dependency.
     *
     * @param \ReflectionParameter $dependency
     *
     * @return mixed
     */
    protected function getParameterOverride(ReflectionParameter $dependency)
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride(): array
    {
        return count($this->parameterOverride) ? end($this->parameterOverride) : [];
    }
}
