<?php

namespace Emberfuse\Tests\Container;

use PHPUnit\Framework\TestCase;
use Emberfuse\Container\Container;

class ContainerTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::setInstance(null);
    }

    public function testGlobalInstance()
    {
        $container = Container::setInstance(new Container());

        $this->assertSame($container, Container::getInstance());

        Container::setInstance(null);

        $container2 = Container::getInstance();

        $this->assertInstanceOf(Container::class, $container2);
        $this->assertNotSame($container, $container2);
    }

    public function testClosureResolution()
    {
        $container = new Container();
        $container->bind('foo', function () {
            return 'bar';
        });

        $this->assertSame('bar', $container->make('foo'));
    }
}
