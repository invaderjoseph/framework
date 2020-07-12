<?php

namespace Emberfuse\Tests\Container;

use PHPUnit\Framework\TestCase;
use Emberfuse\Container\Container;

class ContainerTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::makeInstance(null);
    }

    public function testContainerSingleton()
    {
        $originalContainer = new Container();

        Container::makeInstance($originalContainer);

        $this->assertSame($originalContainer, Container::instance());
    }

    public function testClosureResolution()
    {
        $container = new Container();
        $container->bind('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $container->make('foo'));
    }
}
