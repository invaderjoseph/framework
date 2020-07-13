<?php

namespace Emberfuse\Tests\Container\Stubs;

class ContainerDefaultValueStub
{
    public $stub;
    public $default;

    public function __construct(ContainerConcreteStub $stub, $default = 'foobar')
    {
        $this->stub = $stub;
        $this->default = $default;
    }
}
