<?php

namespace Emberfuse\Base\Concerns;

use Emberfuse\Base\Logger;
use Psr\Log\LoggerInterface;
use Monolog\Logger as MonologLogger;
use Emberfuse\Base\Contracts\ApplicationInterface;

trait RegisterLoggingService
{
    /**
     * Bootstrap logger component.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    protected function registerLoggerService(): ApplicationInterface
    {
        $this->singleton(LoggerInterface::class, function ($app) {
            return $this->createLogger($app);
        });

        return $this;
    }

    /**
     * Create concrete implementation of logger.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface $app
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createLogger(ApplicationInterface $app): LoggerInterface
    {
        $logger = new Logger(new MonologLogger('emberfuse'));

        $logger->useFiles($app->basePath('logs/app.log'), 'debug');

        return $logger;
    }
}
