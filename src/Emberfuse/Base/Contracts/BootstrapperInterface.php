<?php

namespace Emberfuse\Base\Contracts;

interface BootstrapperInterface
{
    /**
     * Bootstrap application.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface
     *
     * @return void
     */
    public function bootstrap(ApplicationInterface $app): void;
}
