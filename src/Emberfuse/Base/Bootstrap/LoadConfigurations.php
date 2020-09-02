<?php

namespace Emberfuse\Base\Bootstrap;

use Symfony\Component\Yaml\Yaml;
use Emberfuse\Support\Repository;
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

        $this->loadConfigurationFile($app, $config);

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
    protected function loadConfigurationFile(ApplicationInterface $app, RepositoryInterface $config)
    {
        foreach (Yaml::parseFile($app->basePath('config.yaml')) as $key => $value) {
            $config->set($key, $value);
        }
    }
}
