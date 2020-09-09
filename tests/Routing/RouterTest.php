<?php

namespace Emberfuse\Tests\Routing;

use stdClass;
use Exception;
use Emberfuse\Routing\Router;
use Emberfuse\Tests\TestCase;
use Emberfuse\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Routing\Contracts\RouterInterface;
use Emberfuse\Container\Exceptions\DependencyResolutionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $router->get('foo/{name}', '\Emberfuse\Tests\Routing\Stubs\MockController@show');
        $this->assertEquals('thavarshan', $router->dispatch(Request::create('foo/thavarshan', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/{name}/{age?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showWithOptions');
        $this->assertEquals('thavarshan25', $router->dispatch(Request::create('foo/thavarshan', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/{name}/boom/{age?}/{location?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showWithThreeOptions');
        $this->assertEquals('thavarshan24SL', $router->dispatch(Request::create('foo/thavarshan/boom/24', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('{name}/{age?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showParams');
        $this->assertEquals('thavarshan25', $router->dispatch(Request::create('thavarshan', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('{age?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showOptionalParams');
        $this->assertEquals('25', $router->dispatch(Request::create('/', 'GET'))->getContent());
        $this->assertEquals('30', $router->dispatch(Request::create('30', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('{name?}/{age?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showTwoOptionalParams');
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

        $router = $this->getRouter();
        $router->get('foo/{file}', '\Emberfuse\Tests\Routing\Stubs\MockController@fileMethod');
        $this->assertEquals('oxygen%20', $router->dispatch(Request::create('http://test.com/foo/oxygen%2520', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->patch('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@patchMethod');
        $this->assertEquals('bar', $router->dispatch(Request::create('foo/bar', 'PATCH'))->getContent());

        // $router = $this->getRouter();
        // $router->get('foo/bar', function () {
        //     return 'hello';
        // });
        // $this->assertEmpty($router->dispatch(Request::create('foo/bar', 'HEAD'))->getContent());

        // $router = $this->getRouter();
        // $router->any('foo/bar', function () {
        //     return 'hello';
        // });
        // $this->assertEmpty($router->dispatch(Request::create('foo/bar', 'HEAD'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@overwriteMethodFirst');
        $router->get('foo/bar', '\Emberfuse\Tests\Routing\Stubs\MockController@overwriteMethodSecond');
        $this->assertEquals('second', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('foo/bar/åαф', '\Emberfuse\Tests\Routing\Stubs\MockController@urlEncodeMock');
        $this->assertEquals('hello', $router->dispatch(Request::create('foo/bar/%C3%A5%CE%B1%D1%84', 'GET'))->getContent());
    }

    public function testNotModifiedResponseIsProperlyReturned()
    {
        $router = $this->getRouter();
        $router->get('test', '\Emberfuse\Tests\Routing\Stubs\MockController@notModifiedResponseMethod');

        $response = $router->dispatch(Request::create('test', 'GET'));
        $this->assertSame(304, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
        $this->assertSame('bar', $response->headers->get('foo'));
        $this->assertNull($response->getLastModified());
    }

    public function testClassesCanBeInjectedIntoRoutes()
    {
        unset($_SERVER['__test.route_inject']);
        $router = $this->getRouter();
        $router->get('foo/{var}', '\Emberfuse\Tests\Routing\Stubs\MockController@mockInjectionMethos');

        $this->assertEquals('hello', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());
        $this->assertInstanceOf(stdClass::class, $_SERVER['__test.route_inject'][0]);
        $this->assertEquals('bar', $_SERVER['__test.route_inject'][1]);

        unset($_SERVER['__test.route_inject']);
    }

    public function testControllerCallActionMethodParameters()
    {
        $router = $this->getRouter();

        // Has one argument but receives two
        unset($_SERVER['__test.controller_callAction_parameters']);
        $router->get(($str = '6MNbWgQ8H3j2UesG') . '/{one}/{two}', '\Emberfuse\Tests\Routing\Stubs\MockControllerWithParameterStub@oneArgument');
        $router->dispatch(Request::create($str . '/one/two', 'GET'));
        $this->assertEquals([0 => 'one'], $_SERVER['__test.controller_callAction_parameters']);

        // Has two arguments and receives two
        unset($_SERVER['__test.controller_callAction_parameters']);
        $router->get(($str = 'P4420W5hRoRKxFx1') . '/{one}/{two}', '\Emberfuse\Tests\Routing\Stubs\MockControllerWithParameterStub@twoArguments');
        $router->dispatch(Request::create($str . '/one/two', 'GET'));
        $this->assertEquals([0 => 'one', 1 => 'two'], $_SERVER['__test.controller_callAction_parameters']);
    }

    public function testThrowsExceptionIfRouteActionMethodDependenciesAreUnresolvable()
    {
        $router = $this->getRouter();

        // Has two arguments but with different names from the ones passed from the route
        try {
            unset($_SERVER['__test.controller_callAction_parameters']);
            $router->get(($str = 'WdfFqaN1xEuqcqfy') . '/{one}/{two}', '\Emberfuse\Tests\Routing\Stubs\MockControllerWithParameterStub@differentArgumentNames');
            $router->dispatch(Request::create($str . '/one/two', 'GET'));
        } catch (Exception $e) {
            $this->assertInstanceOf(DependencyResolutionException::class, $e);
        }

        // Has two arguments with same name but argument order is reversed
        try {
            unset($_SERVER['__test.controller_callAction_parameters']);
            $router->get(($str = 'ztUpdUSkEG8BtECr') . '/{one}/{two}', '\Emberfuse\Tests\Routing\Stubs\MockControllerWithParameterStub@reversedArguments');
            $router->dispatch(Request::create($str . '/one/two', 'GET'));
        } catch (Exception $e) {
            $this->assertInstanceOf(DependencyResolutionException::class, $e);
        }

        // No route parameters while method has parameters
        try {
            unset($_SERVER['__test.controller_callAction_parameters']);
            $router->get(($str = 'XhZalwGdqHADhIup') . '', '\Emberfuse\Tests\Routing\Stubs\MockControllerWithParameterStub@oneArgument');
            $router->dispatch(Request::create($str, 'GET'));
        } catch (Exception $e) {
            $this->assertInstanceOf(DependencyResolutionException::class, $e);
        }
    }

    public function testRoutesDontMatchNonMatchingPathsWithLeadingOptionals()
    {
        $this->expectException(NotFoundHttpException::class);

        $router = $this->getRouter();
        $router->get('{baz?}', '\Emberfuse\Tests\Routing\Stubs\MockController@showOptionalParams');
        $this->assertEquals('25', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());
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
