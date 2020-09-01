<?php

namespace Emberfuse\Base\Contracts;

interface ServiceInterface
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
}
