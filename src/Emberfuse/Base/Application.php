<?php

namespace Emberfuse\Base;

use BadMethodCallException;
use Psr\Log\LoggerInterface;
use Emberfuse\Routing\Router;
use Emberfuse\Container\Container;
use Emberfuse\Base\Contracts\ServiceInterface;
use Emberfuse\Routing\Contracts\RouterInterface;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Emberfuse\Base\Contracts\ExceptionHandlerInterface;

class Application extends Container implements ApplicationInterface
{
    use Concerns\RegisterLoggingService;

    /**
     * Root path where Emberfuse application is installed.
     *
     * @var string
     */
    protected $basePath;

    /**
     * All directories within an Emberfuse application installation.
     *
     * @var array
     */
    protected $directories = [
        'app',
        'database',
        'public',
        'logs',
        'views',
    ];

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * Indicates if the application has been bootstrapped.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Instance of Emberfuse router.
     *
     * @var \Emberfuse\Routing\Router
     */
    protected $router;

    /**
     * Instance of LoggerInterface implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * All of the registered service providers.
     *
     * @var array
     */
    protected $services = [];

    /**
     * Create new instance of Emberfuse application.
     *
     * @param string $basePath
     *
     * @return void
     */
    public function __construct(string $basePath)
    {
        $this->setBasePath($basePath)
            ->registerBaseBindings()
            ->registerBaseServices();
    }

    /**
     * Set path of application installation directory.
     *
     * @param string $path
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    public function setBasePath(string $path): ApplicationInterface
    {
        $this->basePath = rtrim($path, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer(): void
    {
        $this->instance('path.base', $this->basePath());

        foreach ($this->getApplicationDirectoreis() as $path) {
            $this->instance('path.' . $path, $this->basePath($path));
        }
    }

    /**
     * All directories within an Emberfuse application installation.
     *
     * @return array
     */
    protected function getApplicationDirectoreis(): array
    {
        return $this->directories;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @param string $basePath
     *
     * @return string
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Bootstrap the application container.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    protected function registerBaseBindings(): ApplicationInterface
    {
        static::makeInstance($this);

        $this->instance('app', $this);

        $this->instance(self::class, $this);

        return $this;
    }

    /**
     * Register base application services.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    protected function registerBaseServices(): ApplicationInterface
    {
        return $this->registerLoggerService()
            ->registerExceptionHandler()
            ->registerRouter();
    }

    /**
     * Bootstrap the router instance.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    public function registerExceptionHandler(): ApplicationInterface
    {
        $this->singleton(ExceptionHandlerInterface::class, function ($app) {
            return new ExceptionHandler($app(LoggerInterface::class));
        });

        return $this;
    }

    /**
     * Bootstrap the router instance.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    public function registerRouter(): ApplicationInterface
    {
        $this->instance(RouterInterface::class, $this->router = new Router($this));

        return $this;
    }

    /**
     * Boots the registered providers.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        array_walk($this->services, function ($service) {
            $this->bootServices($service);
        });

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param \Emberfuse\Base\Contracts\ServiceInterface
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    protected function bootServices(ServiceInterface $service): void
    {
        if (method_exists($service, 'boot')) {
            call_user_func([$service, 'boot']);
        }

        throw new BadMethodCallException();
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Set the state of application bootstrapping process.
     *
     * @param bool $state
     *
     * @return void
     */
    public function setHasBeenBootstrapped(bool $state): void
    {
        $this->hasBeenBootstrapped = $state;
    }

    /**
     * Register service to be loaded on application boot.
     *
     * @param string $service
     *
     * @return void
     */
    public function registerService(string $service): void
    {
        if (!array_key_exists($service, $this->services)) {
            $this->services[$service] = $this->make($service);
        }
    }

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return string|bool
     */
    public function environment()
    {
        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (preg_match('#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '\z' . '#', $this['env'])) {
                    return true;
                }
            }

            return false;
        }

        return $this['env'];
    }

    /**
     * Get instance of Emberfuse routing component.
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->make(LoggerInterface::class) ?? null;
    }

    /**
     * Get instance of Emberfuse routing component.
     *
     * @return \Emberfuse\Routing\Contracts\RouterInterface|null
     */
    public function getRouter(): ?RouterInterface
    {
        return $this->router;
    }

    /**
     * Get application configurations repository.
     *
     * @param string $key
     *
     * @return array
     */
    protected function configurations(string $key): array
    {
        return $this['config'];
    }
}
