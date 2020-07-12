<?php

declare(strict_types=1);

namespace Emberfuse\Container;

use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    /**
     * Globally accessible instance of service container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected static $instance;

    public function get($id)
    {
    }

    public function has($id)
    {
    }

    /**
     * Get globally accessible instance of service container.
     *
     * @return \Psr\Container\ContainerInterface
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Set globally accessible container instance.
     *
     * @param \Psr\Container\ContainerInterface|null $container
     *
     * @return \Psr\Container\ContainerInterface|null
     */
    public static function setInstance(?ContainerInterface $container = null)
    {
        return static::$instance = $container;
    }
}
