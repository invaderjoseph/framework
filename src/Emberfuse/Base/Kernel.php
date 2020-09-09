<?php

namespace Emberfuse\Base;

use Throwable;
use Emberfuse\Support\Pipeline;
use Emberfuse\Base\Bootstrap\LoadServices;
use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Base\Bootstrap\LoadErrorHandler;
use Symfony\Component\HttpFoundation\Response;
use Emberfuse\Base\Bootstrap\LoadConfigurations;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Emberfuse\Base\Bootstrap\LoadEnvironmentVariables;
use Emberfuse\Base\Contracts\ExceptionHandlerInterface;

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
        LoadErrorHandler::class,
        LoadServices::class,
    ];

    /**
     * Application middleware stack.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Create new instance of HTTP Kernel.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface $app
     *
     * @return void
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
        $request = $this->makeRequestInstance($request);

        try {
            $this->bootstrapApplication();

            $this->app->boot();

            $response = $this->sendRequestThroughRouter($request);
        } catch (Throwable $e) {
            if (! $catch) {
                $this->reportException($e);

                throw $e;
            }

            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    /**
     * Modify and bind the HTTP request instance to the application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function makeRequestInstance(Request $request): Request
    {
        $request->headers->set('X-Php-Ob-Level', (string) ob_get_level());

        $request->enableHttpMethodParameterOverride();

        $this->app->instance('request', $request);

        return $request;
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
        return (new Pipeline($this->app))
            ->send($request)
            ->through($this->middleware)
            ->then(function ($request) {
                return $this->app->getRouter()->dispatch($request);
            });
    }

    /**
     * Bootstrap application with registered bootstrapping classes.
     *
     * @return void
     */
    public function bootstrapApplication(): void
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $this->app->make($bootstrapper)->bootstrap($this->app);
        }

        $this->app->setHasBeenBootstrapped(true);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    protected function reportException(Throwable $e): void
    {
        $this->app[ExceptionHandlerInterface::class]->report($e);
    }

    /**
     * Render the exception to a response.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Throwable                                $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderException(Request $request, Throwable $e): Response
    {
        return $this->app[ExceptionHandlerInterface::class]->render($request, $e);
    }
}
