<?php

namespace Emberfuse\Base\Contracts;

interface ApplicationInterface
{
    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath(): string;

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return string|bool
     */
    public function environment();

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot(): void;
}
