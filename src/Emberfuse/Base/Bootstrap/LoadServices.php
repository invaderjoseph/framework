<?php

namespace Emberfuse\Base\Bootstrap;

use Emberfuse\Base\Contracts\BootstrapperInterface;

class LoadServices implements BootstrapperInterface
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
        foreach ($app['config']->get('services') as $service) {
            $app->registerService($service);
        }
    }
}
