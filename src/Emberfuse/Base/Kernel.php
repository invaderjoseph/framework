<?php

namespace Emberfuse\Base;

use Throwable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Emberfuse\Base\Bootstrappers\LoadConfigurations;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Emberfuse\Base\Bootstrappers\LoadEnvironmentVariables;

class Kernel implements HttpKernelInterface
{
    /**
     * All bootstrap classes of application.
     *
     * @var array
     */
    protected $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfigurations::class,
    ];

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

        $this->app->instance('request', $request);

        try {
            $this->bootstrapApplication();

            $this->app->boot();

            $response = $this->sendRequestThroughRouter($request);
        } catch (Throwable $e) {
            if (false === $catch) {
                // $this->reportException($e);

                // throw $e;
            }

            // $response = $this->renderException($request, $e);
        }

        return $response;
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendRequestThroughRouter(Request $request): Response
    {
        return $this->app->router->dispatch($request);
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

    /**
     * Report the exception to the exception handler.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    protected function reportException(Throwable $e)
    {
        $this->app[ExceptionHandler::class]->report($e);
    }

    /**
     * Render the exception to a response.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Throwable                                $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderException(Request $request, Throwable $e)
    {
        return $this->app[ExceptionHandler::class]->render($request, $e);
    }
}
