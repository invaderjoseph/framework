<?php

namespace Emberfuse\Base\Contracts;

use Closure;
use Symfony\Component\HttpFoundation\Request;

interface MiddlewareInterface
{
    /**
     * Handle incoming request instance.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Closure                                  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next);
}
