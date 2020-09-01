<?php

namespace Emberfuse\Base\Contracts;

use Throwable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ExceptionHandlerInterface
{
    /**
     * Report or log an exception.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    public function report(Throwable $e): void;

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Throwable                                $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render(Request $request, Throwable $e): Response;
}
