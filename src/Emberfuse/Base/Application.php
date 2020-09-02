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
use Emberfuse\Base\Exceptions\InvalidServiceClassException;

class Application extends Container implements ApplicationInterface
{
    use Concerns\RegisterLoggingService;
    use Concerns\RegisterErrorHandler;

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
        'config',
        'database',
        'public',
        'storage',
        'resources',
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
            ->registerBaseServices()
            ->registerErrorHandling();
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
        return $this->bootstrapLogger()
            ->bootstrapExceptionHandler()
            ->bootstrapRouter();
    }

    /**
     * Bootstrap the router instance.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    public function bootstrapLogger(): ApplicationInterface
    {
        return $this->registerLoggerService();
    }

    /**
     * Bootstrap the router instance.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    public function bootstrapExceptionHandler(): ApplicationInterface
    {
        $this->singleton(ExceptionHandlerInterface::class, function ($app) {
            return new ExceptionHandler($app[LoggerInterface::class]);
        });

        return $this;
    }

    /**
     * Bootstrap the router instance.
     *
     * @return \Emberfuse\Base\Contracts\ApplicationInterface
     */
    public function bootstrapRouter(): ApplicationInterface
    {
        $this->router = new Router($this);

        $this->singleton(RouterInterface::class, function ($app) {
            return $this->router;
        });

        return $this;
    }

    /**
     * Boots the registered providers.
     *
     * @return void|null
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
     * @param string
     *
     * @return void
     *
     * @throws \Emberfuse\Base\Exceptions\InvalidServiceClassException
     * @throws \BadMethodCallException
     */
    protected function bootServices(string $service): void
    {
        $service = $this->make($service);

        if (!$service instanceof ServiceInterface) {
            throw new InvalidServiceClassException();
        }

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
     * Get instance of emberfuse routing component.
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->make(LoggerInterface::class) ?? null;
    }

    /**
     * Get instance of emberfuse routing component.
     *
     * @return \Emberfuse\Routing\Contarcts\RouterInterface|null
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
