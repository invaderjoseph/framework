<?php

namespace Emberfuse\Base\Concerns;

use Throwable;
use ErrorException;
use Emberfuse\Base\Application;
use Emberfuse\Base\ExceptionHandler;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Emberfuse\Base\Contracts\ExceptionHandlerInterface;

trait RegisterErrorHandler
{
    /**
     * Set the error handling for the application.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    protected function registerErrorHandling(): ApplicationInterface
    {
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

        return $this;
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
     * Send the exception to the handler and return the response.
     *
     * @param \Throwable $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendExceptionToHandler(Throwable $e)
    {
        $handler = $this->resolveExceptionHandler();

        $handler->report($e);

        return $handler->render($this->make('request'), $e);
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

        $handler->render($this->make('request'), $e)->send();
    }

    /**
     * Get the exception handler from the container.
     *
     * @return \Emberfuse\Base\Contracts\ExceptionHandlerInterface
     */
    protected function resolveExceptionHandler(): ExceptionHandlerInterface
    {
        if ($this->bound(ExceptionHandlerInterface::class)) {
            return $this->make(ExceptionHandlerInterface::class);
        }

        return $this->make(ExceptionHandler::class);
    }
}
