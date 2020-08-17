<?php

namespace Emberfuse\Tests\Routing;

use Emberfuse\Routing\Router;
use PHPUnit\Framework\TestCase;
use Emberfuse\Container\Container;
use Symfony\Component\HttpFoundation\Request;

class RouterTest extends TestCase
{
    /**
     * Instance of router.
     *
     * @var \Emberfuse\Routing\Router
     */
    protected $router;

    protected function setUp(): void
    {
        $this->router = new Router(new Container());
    }

    public function testCanRegisterGetRouteWithControllerAction()
    {
        $this->router->get('/', 'MockController@index');

        $this->assertEquals('bar', $this->router->dispatch(Request::create('/', 'GET'))->getContent());
    }
}
