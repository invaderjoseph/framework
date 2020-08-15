<?php

namespace Emberfuse\Tests\Container\Stubs;

class ContainerMixedPrimitiveStub
{
    public $first;
    public $last;
    public $stub;

    public function __construct($first, ContainerConcreteStub $stub, $last)
    {
        $this->stub  = $stub;
        $this->last  = $last;
        $this->first = $first;
    }
}
