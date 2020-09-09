<?php

namespace Emberfuse\Tests\Routing\Stubs;

use Emberfuse\Routing\Controller;

class MockControllerWithParameterStub extends Controller
{
    public function callAction($method, $parameters)
    {
        $_SERVER['__test.controller_callAction_parameters'] = $parameters;
    }

    public function oneArgument($one)
    {
    }

    public function twoArguments($one, $two)
    {
    }

    public function differentArgumentNames($bar, $baz)
    {
    }

    public function reversedArguments($two, $one)
    {
    }
}
