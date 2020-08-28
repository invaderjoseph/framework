<?php

namespace Emberfuse\Tests\Routing;

use Exception;
use Emberfuse\Routing\Route;
use Emberfuse\Tests\TestCase;
use Emberfuse\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class RouteCollectionTest extends TestCase
{
    public function testAddGivenRouteToCollection()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add(new Route('GET', 'foo', 'FooController@bar'));

        $this->assertCount(1, $routeCollection);
        $this->assertCount(1, $routeCollection->getRoutes());
    }

    public function testMatchesGivenRequestWithRoutes()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add($route = new Route('GET', 'foo', 'FooController@bar'));

        $this->assertSame($route, $routeCollection->match(Request::create('foo', 'GET')));
    }

    public function testThrowsNotFoundExceprtionIfNoMatchFound()
    {
        $routeCollection = new RouteCollection();
        $nonRegisteredRoute = new Route('GET', 'fum', 'FumController@boo');
        $routeCollection->add($route = new Route('GET', 'foo', 'FooController@bar'));

        try {
            $routeCollection->match(Request::create('fum', 'GET'));
        } catch (Exception $e) {
            $this->assertInstanceOf(RouteNotFoundException::class, $e);
            $this->assertCount(1, $routeCollection);

            return;
        }

        $this->fail();
    }
}
