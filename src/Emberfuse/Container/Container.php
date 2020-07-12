<?php

declare(strict_types=1);

namespace Emberfuse\Container;

use Closure;
use Exception;
use Psr\Container\ContainerInterface;
use Emberfuse\Container\Exceptions\EntryNotFoundException;
use Emberfuse\Container\Exceptions\BindingResolutionException;

class Container implements ContainerInterface
{
    /**
     * The container's bindings.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * Globally set container instance.
     *
     * @var \Psr\Container\ContainerInterface|null
     */
    protected static $instance;

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        try {
            return $this->make($id);
        } catch (Exception $exception) {
            if ($this->has($id)) {
                throw $exception;
            }

            throw new EntryNotFoundException($id, $exception->getCode(), $exception);
        }
    }

    /**
     * Resolve the requested binding.
     *
     * @param string $alias
     *
     * @return \Closure
     */
    public function make(string $alias)
    {
        // Resolve the closure object and return the result.
        return call_user_func($this->bindings[$alias]);

        // If an entry was not found throw a not fount exception
        throw new BindingResolutionException();
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return array_key_exists($id, $this->bindings);
    }

    /**
     * Bind an abstract type to the container.
     *
     * @param string   $alias
     * @param \Closure $binding
     *
     * @return void
     */
    public function bind(string $alias, Closure $binding): void
    {
        // Determine if the requested binding already exists in the container.
        if ($this->has($alias)) {
            // If the binding already exists, remove it from the container.
            $this->flush($alias);
        }

        // Make a fresh entry of the binding in the container.
        $this->bindings[$alias] = $binding;
    }

    /**
     * Remove the given entry from the container.
     *
     * @param string $alias
     *
     * @return void
     */
    public function flush(string $alias): void
    {
        $this->bindings[$alias] = null;

        unset($this->bindings[$alias]);
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return \Psr\Container\ContainerInterface
     */
    public static function instance(): ContainerInterface
    {
        // Check if global instance is available.
        if (is_null(static::$instance)) {
            // If not set new instance of self.
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Set globally available instance of the container.
     *
     * @param \Psr\Container\ContainerInterface|null $container
     *
     * @return \Psr\Container\ContainerInterface|null
     */
    public static function makeInstance(?ContainerInterface $container = null)
    {
        return static::$instance = $container;
    }
}
