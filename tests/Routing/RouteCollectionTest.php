<?php

namespace Emberfuse\Tests\Routing;

use Emberfuse\Routing\Route;
use PHPUnit\Framework\TestCase;
use Emberfuse\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteCollectionTest extends TestCase
{
    /**
     * @var \Emberfuse\Routing\RouteCollection
     */
    protected $routeCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeCollection = new RouteCollection();
    }

    public function testRouteCollectionCanAddRoute()
    {
        $this->routeCollection->add(new Route('GET', 'foo', 'FooController@index'));

        $this->assertCount(1, $this->routeCollection->getRoutesList());
    }

    public function testRouteCollectionAddReturnsTheRoute()
    {
        $route = $this->routeCollection->add(
            $inputRoute = new Route('GET', 'foo', 'FooController@index')
        );

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('GET', $route->method());
        $this->assertEquals('foo', $route->uri());
        $this->assertEquals($inputRoute, $route);
    }

    public function testRouteCollectionCanHandleSameRoute()
    {
        $routeIndex = new Route('GET', 'foo/index', 'FooController@index');

        $this->routeCollection->add($routeIndex);
        $this->assertCount(1, $this->routeCollection->getRoutesList());

        // Add exactly the same route
        $this->routeCollection->add($routeIndex);
        $this->assertCount(1, $this->routeCollection->getRoutesList());

        // Add a non-existing route
        $this->routeCollection->add(new Route('GET', 'bar/show', 'BarController@show'));
        $this->assertCount(2, $this->routeCollection->getRoutesList());
    }

    public function testRouteCollectionCanGetAllRoutes()
    {
        $this->routeCollection->add($routeIndex = new Route('GET', 'foo/index', 'FooController@index'));
        $this->routeCollection->add($routeShow = new Route('GET', 'foo/show', 'FooController@show'));
        $this->routeCollection->add($routeNew = new Route('POST', 'bar', 'BarController@create'));

        $allRoutes = [
            $routeIndex,
            $routeShow,
            $routeNew,
        ];
        $this->assertEquals($allRoutes, array_values($this->routeCollection->getRoutesList()));
    }

    public function testRouteCollectionDontMatchNonMatchingDoubleSlashes()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->routeCollection->add(new Route('GET', 'foo', 'FooController@index'));

        $request = Request::create('', 'GET');

        // URI must be set in REQUEST_URI otherwise Request uses parse_url() which will trim the slashes.
        $request->server->set('REQUEST_URI', '//foo');
        $this->routeCollection->match($request);
    }
}
