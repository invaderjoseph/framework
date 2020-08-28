<?php

namespace Emberfuse\Tests\Routing;

use Emberfuse\Routing\Router;
use Emberfuse\Tests\TestCase;
use Emberfuse\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Routing\Contracts\RouterInterface;

class RouterTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $router = $this->getRouter();

        $this->assertInstanceOf(RouterInterface::class, $router);
    }

    public function testRegisterBasicRoutes()
    {
        $router = $this->getRouter();
        $router->addRoute('GET', '/foo', 'MockController@foo');

        $this->assertCount(1, $router->getRouteCollection());
    }

    public function testBasicDispatchingOfRoutes()
    {
        $router = $this->getRouter();
        $router->get('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@index');
        $this->assertEquals('bar', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@index');
        $router->post('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@store');
        $this->assertEquals('bar', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());
        $this->assertEquals('post bar', $router->dispatch(Request::create('foo/bar', 'POST'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/{bar}', '\Emberfuse\Tests\Routing\Stubs\MockController@show');
        $this->assertEquals('thavarshan', $router->dispatch(Request::create('foo/thavarshan', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/{bar}/{baz?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showWithOptions');
        $this->assertEquals('thavarshan25', $router->dispatch(Request::create('foo/thavarshan', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/{name}/boom/{age?}/{location?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showWithThreeOptions');
        $this->assertEquals('thavarshan24SL', $router->dispatch(Request::create('foo/thavarshan/boom/24', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('{bar}/{baz?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showParams');
        $this->assertEquals('thavarshan25', $router->dispatch(Request::create('thavarshan', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('{baz?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showOptionalParams');
        $this->assertEquals('25', $router->dispatch(Request::create('/', 'GET'))->getContent());
        $this->assertEquals('30', $router->dispatch(Request::create('30', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('{foo?}/{baz?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showTwoOptionalParams');
        $this->assertEquals('thavarshan25', $router->dispatch(Request::create('/', 'GET'))->getContent());
        $this->assertEquals('navin25', $router->dispatch(Request::create('navin', 'GET'))->getContent());
        $this->assertEquals('navin30', $router->dispatch(Request::create('navin/30', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@index');
        $router->get('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@store');
        $this->assertEquals('post bar', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/bar/åαф', '\Emberfuse\Tests\Routing\Stubs\MockController@hello');
        $this->assertEquals('hello', $router->dispatch(Request::create('foo/bar/%C3%A5%CE%B1%D1%84', 'GET'))->getContent());
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
