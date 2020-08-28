<?php

namespace Emberfuse\Tests\Routing;

use Emberfuse\Tests\TestCase;
use Emberfuse\Routing\RouteAction;

class RouteActionTest extends TestCase
{
    public function testParseRouteAction()
    {
        $this->assertEquals(
            [
                'controller' => 'FooController',
                'method' => 'bar',
            ],
            RouteAction::parse('FooController@bar')
        );
    }
}
