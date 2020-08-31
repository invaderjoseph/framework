<?php

namespace Emberfuse\Base;

use Throwable;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Kernel implements HttpKernelInterface
{
    protected $bootstrappers = [];

    /**
     * Create new instance of Http Kernel.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface $app [description]
     */
    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, int $type = HttpKernelInterface::MASTER_REQUEST, bool $catch = true)
    {
        $request->headers->set('X-Php-Ob-Level', (string) ob_get_level());

        $this->bootstrapApplication();

        try {
            $this->boot();

            // $response = $this->sendRequestThroughRouter($request);
        } catch (Throwable $e) {
            if (false === $catch) {
                // $this->reportException($e);

                // throw $e;
            }

            // $response = $this->renderException($request, $e);
        }

        // return $this->prepareResponse($response);
    }

    /**
     * Bootstrap application with registered bootstrapping classes.
     *
     * @return void
     */
    protected function bootstrapApplication(): void
    {
        $this->app->setHasBeenBootstrapped(true);

        foreach ($this->bootstrappers as $bootstrapper) {
            $this->app->make($bootstrappers)->bootstrap();
        }
    }
}
