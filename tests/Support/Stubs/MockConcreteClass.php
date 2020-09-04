<?php

namespace Emberfuse\Tests\Support\Stubs;

class MockConcreteClass
{
    /**
     * Mock name dependency.
     *
     * @var string
     */
    protected $name;

    /**
     * Create new mock concrete class instance.
     *
     * @param string $name
     */
    public function __construct(string $name = 'Thavarshan')
    {
        $this->name = $name;
    }
}
