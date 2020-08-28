<?php

namespace Emberfuse\Tests\Routing;

use Emberfuse\Routing\Route;
use Emberfuse\Tests\TestCase;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\HttpFoundation\Request;

class RouteTest extends TestCase
{
    public function testNormalizesGivenUri()
    {
        $route = new Route('GET', '/foo/bar', 'FooController@bar');

        $this->assertEquals('foo/bar', $route->prefixUri('/foo/bar'));
        $this->assertEquals('foo/bar', $route->uri());
    }

    public function testParseRouteAction()
    {
        $route = new Route('GET', 'foo/bar', 'FooController@bar');

        $this->assertEquals(
            [
                'controller' => 'FooController',
                'method' => 'bar',
            ],
            $route->getAction()
        );
    }

    public function testCompileRoute()
    {
        $route = new Route('GET', 'foo/bar', 'FooController@bar');

        $this->assertNull($route->getCompiled());

        $route->compile();

        $this->assertInstanceOf(CompiledRoute::class, $route->getCompiled());
    }

    public function testMatchesRouteWithGivenRequest()
    {
        $routeMain = new Route('GET', '/', 'FooController@bar');
        $routeFooBar = new Route('GET', 'foo/bar', 'FooController@bar');

        $this->assertTrue($routeMain->matches(Request::create('/', 'GET')));
        $this->assertTrue($routeFooBar->matches(Request::create('foo/bar', 'GET')));
    }
}
