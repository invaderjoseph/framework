<?php

namespace Emberfuse\Base\Bootstrap;

use Symfony\Component\Yaml\Yaml;
use Emberfuse\Support\Repository;
use Symfony\Component\Finder\Finder;
use Emberfuse\Base\Contracts\ApplicationInterface;
use Emberfuse\Base\Contracts\BootstrapperInterface;
use Emberfuse\Support\Contracts\RepositoryInterface;

class LoadConfigurations implements BootstrapperInterface
{
    /**
     * Bootstrap application.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface
     *
     * @return void
     */
    public function bootstrap(ApplicationInterface $app): void
    {
        $items = [];

        $app->instance('config', $config = new Repository($items));

        $this->loadConfigurationFiles($app, $config);

        date_default_timezone_set($config['app.timezone'] ?? 'UTC');

        mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface $app
     * @param \Illuminate\Contracts\Config\Repository        $config
     *
     * @return void
     */
    protected function loadConfigurationFiles(ApplicationInterface $app, RepositoryInterface $config)
    {
        foreach ($this->getConfigurationFiles($app) as $key => $file) {
            $config->set($key, Yaml::parseFile($file));
        }
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param \Emberfuse\Base\Contracts\ApplicationInterface $app
     *
     * @return array
     */
    protected function getConfigurationFiles(ApplicationInterface $app): array
    {
        $files = [];

        $yamlFiles = Finder::create()
            ->files()
            ->name('*.yaml')
            ->in($app->basePath('config'));

        foreach ($yamlFiles as $file) {
            $files[basename($file->getRealPath(), '.yaml')] = $file->getRealPath();
        }

        return $files;
    }
}
