<?php

namespace Emberfuse\Base\Bootstrap;

use Throwable;
use ErrorException;
use Psr\Log\LoggerInterface;
use Emberfuse\Base\ExceptionHandler;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Emberfuse\Base\Contracts\BootstrapperInterface;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Emberfuse\Base\Contracts\ExceptionHandlerInterface;

class LoadErrorHandler implements BootstrapperInterface
{
    /**
     * Instance of Emberfuse application.
     *
     * @var \Emberfuse\Base\Contracts\ApplicationInterface
     */
    protected $app;

    /**
     * Bootstrap application.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface
     *
     * @return void
     */
    public function bootstrap(ApplicationInterface $app): void
    {
        $this->app = $app;

        $this->bootstrapExceptionHandler();

        error_reporting(-1);

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });

        set_exception_handler(function ($e) {
            $this->handleException($e);
        });

        register_shutdown_function(function () {
            $this->handleShutdown();
        });
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown(): void
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalErrorFromPhpError($error, 0));
        }
    }

    /**
     * Create a new fatal error instance from an error array.
     *
     * @param array    $error
     * @param int|null $traceOffset
     *
     * @return \Symfony\Component\ErrorHandler\Error\FatalError
     */
    protected function fatalErrorFromPhpError(array $error, ?int $traceOffset = null): FatalError
    {
        return new FatalError($error['message'], 0, $error, $traceOffset);
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param int $type
     *
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

    /**
     * Handle an uncaught exception instance.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    protected function handleException(Throwable $e)
    {
        $handler = $this->resolveExceptionHandler();

        $handler->report($e);

        $handler->render($this->app->make('request'), $e)->send();
    }

    /**
     * Get the exception handler from the container.
     *
     * @return \Emberfuse\Base\Contracts\ExceptionHandlerInterface
     */
    protected function resolveExceptionHandler(): ExceptionHandlerInterface
    {
        if ($this->app->has(ExceptionHandlerInterface::class)) {
            return $this->app->make(ExceptionHandlerInterface::class);
        }

        return $this->app->make(ExceptionHandler::class);
    }

    /**
     * Bootstrap the exception handler instance.
     *
     * @return void
     */
    protected function bootstrapExceptionHandler(): void
    {
        $handler = new ExceptionHandler($this->app->make(LoggerInterface::class));

        $this->app->bind(ExceptionHandler::class, function ($app) use ($handler) {
            return $handler;
        });

        $this->app->instance(ExceptionHandlerInterface::class, $handler);
    }
}
