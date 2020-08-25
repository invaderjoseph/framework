<?php

namespace Emberfuse\Tests\Routing;

use Emberfuse\Routing\Route;
use PHPUnit\Framework\TestCase;
use Emberfuse\Routing\RouteCollection;

class RouteCollectionTest extends TestCase
{
    public function testItAddsGivenRouteToCollection()
    {
        $routeCollection = new RouteCollection();

        $routeCollection->add(new Route('GET', 'foo', 'FooController@bar'));

        $this->assertCount(1, $routeCollection);
        $this->assertCount(1, $routeCollection->getRoutes());
    }
}
