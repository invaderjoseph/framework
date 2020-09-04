<?php

namespace Emberfuse\Tests\Container\Stubs;

use stdClass;

class ContainerConcreteStub
{
    public function stubMethod(stdClass $stdObject, string $default = 'Thavarshan'): array
    {
        return [$stdObject, $default];
    }
}
