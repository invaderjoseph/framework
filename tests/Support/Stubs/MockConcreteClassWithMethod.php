<?php

namespace Emberfuse\Tests\Support\Stubs;

use stdClass;

class MockConcreteClassWithMethod
{
    /**
     * Mock class method.
     *
     * @param \stdClass $parameter
     *
     * @return \stdClass
     */
    public function mockMethod(stdClass $mockClass): stdClass
    {
        return $mockClass;
    }
}
