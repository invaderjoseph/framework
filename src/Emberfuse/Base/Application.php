<?php

namespace Emberfuse\Base;

use Emberfuse\Routing\Router;
use Emberfuse\Container\Container;
use Emberfuse\Base\Contracts\ApplicationInterface;

class Application extends Container implements ApplicationInterface
{
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
            ->registerErrorHandling()
            ->bootstrapLogger()
            ->bootstrapRouter();
    }

    /**
     * Set path of application installation directory.
     *
     * @param string $path
     *
     * @return \Emberfuse\Base\Application
     */
    public function setBasePath(string $path): Application
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
     * @return \Emberfuse\Base\Application
     */
    protected function registerBaseBindings(): Application
    {
        static::makeInstance($this);

        $this->instance('app', $this);

        $this->instance(self::class, $this);

        return $this;
    }

    /**
     * Bootstrap the router instance.
     *
     * @return \Emberfuse\Base\Application
     */
    public function bootstrapLogger(): Application
    {
        // $this->logger = new Logger($this['config']['logging']);

        return $this;
    }

    /**
     * Bootstrap the router instance.
     *
     * @return \Emberfuse\Base\Application
     */
    public function bootstrapRouter(): Application
    {
        $this->router = new Router($this);

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
}