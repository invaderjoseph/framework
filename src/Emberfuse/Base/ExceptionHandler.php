<?php

namespace Emberfuse\Base;

use Throwable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Emberfuse\Base\Contracts\ExceptionHandlerInterface;
use Symfony\Component\Debug\ExceptionHandler as SymfonyDisplayer;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * Instance of logger implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create new exception handler instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    public function report(Throwable $e): void
    {
        $this->logger->error($e->getMessage(), ['exception' => $e]);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Throwable $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render(Throwable $e): Response
    {
        return (new SymfonyDisplayer(getenv('APP_ENV')))->sendPhpResponse($e);
    }
}
