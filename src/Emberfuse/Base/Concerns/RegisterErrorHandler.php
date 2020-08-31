<?php

namespace Emberfuse\Base\Concerns;

use Emberfuse\Base\Application;

trait RegisterErrorHandler
{
    /**
     * Set the error handling for the application.
     *
     * @return \Emberfuse\Base\Application
     */
    protected function registerErrorHandling(): Application
    {
        return $this;
    }
}
