<?php

namespace Emberfuse\Tests\Container;

use stdClass;
use PHPUnit\Framework\TestCase;
use Emberfuse\Container\Container;
use Emberfuse\Tests\Container\Stubs\ContainerConcreteStub;
use Emberfuse\Tests\Container\Stubs\ContainerDependentStub;
use Emberfuse\Tests\Container\Stubs\IContainerContractStub;
use Emberfuse\Tests\Container\Stubs\ContainerImplementationStub;
use Emberfuse\Tests\Container\Stubs\ContainerNestedDependentStub;

class ContainerTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::makeInstance(null);
    }

    public function testContainerSingleton()
    {
        $container = Container::makeInstance(new Container());

        $this->assertSame($container, Container::getInstance());

        Container::makeInstance(null);

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

    public function testSharedClosureResolution()
    {
        $container = new Container();
        $container->singleton('class', function () {
            return new stdClass();
        });

        $firstInstantiation  = $container->make('class');
        $secondInstantiation = $container->make('class');

        $this->assertSame($firstInstantiation, $secondInstantiation);
    }

    public function testAutoConcreteResolution()
    {
        $container = new Container();
        $this->assertInstanceOf(ContainerConcreteStub::class, $container->make(ContainerConcreteStub::class));
    }

    public function testSharedConcreteResolution()
    {
        $container = new Container();
        $container->singleton(ContainerConcreteStub::class);

        $var1 = $container->make(ContainerConcreteStub::class);
        $var2 = $container->make(ContainerConcreteStub::class);
        $this->assertSame($var1, $var2);
    }

    public function testAbstractToConcreteResolution()
    {
        $container = new Container();
        $container->bind(IContainerContractStub::class, ContainerImplementationStub::class);
        $class = $container->make(ContainerDependentStub::class);
        $this->assertInstanceOf(ContainerImplementationStub::class, $class->impl);
    }

    public function testNestedDependencyResolution()
    {
        $container = new Container();
        $container->bind(IContainerContractStub::class, ContainerImplementationStub::class);
        $class = $container->make(ContainerNestedDependentStub::class);
        $this->assertInstanceOf(ContainerDependentStub::class, $class->inner);
        $this->assertInstanceOf(ContainerImplementationStub::class, $class->inner->impl);
    }

    public function testContainerIsPassedToResolvers()
    {
        $container = new Container();
        $container->bind('something', function ($c) {
            return $c;
        });
        $c = $container->make('something');
        $this->assertSame($c, $container);
    }
}
