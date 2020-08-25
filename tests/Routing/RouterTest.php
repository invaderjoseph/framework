<?php

namespace Emberfuse\Tests\Routing;

use Emberfuse\Routing\Router;
use PHPUnit\Framework\TestCase;
use Emberfuse\Container\Container;
use Emberfuse\Routing\Contracts\RouterInterface;

class RouterTest extends TestCase
{
    public function testItCanBeInstantiated()
    {
        $router = $this->getRouter();

        $this->assertInstanceOf(RouterInterface::class, $router);
    }

    public function testItCanRegisterRoutes()
    {
        $router = $this->getRouter();

        $router->addRoute('GET', '/foo', 'MockController@foo');

        $this->assertCount(1, $router->getRouteCollection());
    }

    /**
     * Get instance of Emberfuse router.
     *
     * @return \Emberfus\Routing\RouterInterface
     */
    protected function getRouter(): RouterInterface
    {
        $container = Container::makeInstance(new Container());

        return new Router($container);
    }
}
