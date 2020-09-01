<?php

namespace Emberfuse\Base\Bootstrap;

use Dotenv\Dotenv;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Emberfuse\Base\Contracts\BootstrapperInterface;

class LoadEnvironmentVariables implements BootstrapperInterface
{
    /**
     * Bootstrap application.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface
     *
     * @return void
     */
    public function bootstrap(ApplicationInterface $app): void
    {
        Dotenv::createUnsafeImmutable($app->basePath())->safeLoad();

        $app->instance('env', $_ENV['APP_ENV']);
    }
}
