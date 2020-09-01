<?php

namespace Emberfuse\Support\Contracts;

interface RepositoryInterface
{
    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get the specified configuration value.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed        $value
     *
     * @return void
     */
    public function set(string $key, $value = null): void;

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function prepend(string $key, $value): void;

    /**
     * Push a value onto an array configuration value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function push(string $key, $value): void;
}
