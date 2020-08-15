<?php

namespace Emberfuse\Tests\Container\Stubs;

class ContainerDependentStub
{
    public $impl;

    public function __construct(IContainerContractStub $impl)
    {
        $this->impl = $impl;
    }
}
