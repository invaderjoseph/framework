<?php

namespace Emberfuse\Tests\Container;

use stdClass;
use PHPUnit\Framework\TestCase;
use Emberfuse\Container\Container;
use Psr\Container\ContainerExceptionInterface;
use Emberfuse\Tests\Container\Stubs\ContainerConcreteStub;
use Emberfuse\Tests\Container\Stubs\ContainerDependentStub;
use Emberfuse\Tests\Container\Stubs\IContainerContractStub;
use Emberfuse\Container\Exceptions\BindingNotFoundException;
use Emberfuse\Container\Exceptions\BindingResolutionException;
use Emberfuse\Tests\Container\Stubs\ContainerDefaultValueStub;
use Emberfuse\Tests\Container\Stubs\ContainerImplementationStub;
use Emberfuse\Tests\Container\Stubs\ContainerNestedDependentStub;
use Emberfuse\Tests\Container\Stubs\ContainerInjectVariableStubWithInterfaceImplementation;

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

        $firstInstantiation = $container->make('class');
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

    public function testArrayAccess()
    {
        $container = new Container();
        $container['something'] = function () {
            return 'foo';
        };
        $this->assertTrue(isset($container['something']));
        $this->assertSame('foo', $container['something']);
        unset($container['something']);
        $this->assertFalse(isset($container['something']));
    }

    public function testBindingsCanBeOverridden()
    {
        $container = new Container();
        $container['foo'] = 'bar';
        $container['foo'] = 'baz';

        $this->assertSame('baz', $container['foo']);
    }

    public function testBindingAnInstanceReturnsTheInstance()
    {
        $container = new Container();

        $bound = new stdClass();
        $resolved = $container->instance('foo', $bound);

        $this->assertSame($bound, $resolved);
    }

    public function testBindingAnInstanceAsShared()
    {
        $container = new Container();
        $bound = new stdClass();
        $container->instance('foo', $bound);
        $object = $container->make('foo');
        $this->assertSame($bound, $object);
    }

    public function testResolutionOfDefaultParameters()
    {
        $container = new Container();
        $instance = $container->make(ContainerDefaultValueStub::class);
        $this->assertInstanceOf(ContainerConcreteStub::class, $instance->stub);
        $this->assertSame('foobar', $instance->default);
    }

    public function testUnsetRemoveBoundInstances()
    {
        $container = new Container();
        $container->instance('object', new stdClass());
        unset($container['object']);

        $this->assertFalse($container->has('object'));
    }

    public function testInternalClassWithDefaultParameters()
    {
        $this->expectException(BindingResolutionException::class);

        $container = new Container();
        $container->make(ContainerMixedPrimitiveStub::class, []);
    }

    public function testBindingResolutionException()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage("[Emberfuse\Tests\Container\Stubs\IContainerContractStub] is not instantiable.");

        $container = new Container();
        $container->make(IContainerContractStub::class, []);
    }

    public function testBindingResolutionExceptionWhenClassDoesNotExist()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage("Class [Foo\Bar\Baz\DummyClass] does not exist.");

        $container = new Container();
        $container->build('Foo\Bar\Baz\DummyClass');
    }

    public function testForgetInstanceForgetsInstance()
    {
        $container = new Container();
        $containerConcreteStub = new ContainerConcreteStub();
        $container->instance(ContainerConcreteStub::class, $containerConcreteStub);
        $this->assertTrue($container->isShared(ContainerConcreteStub::class));
        $container->forgetInstance(ContainerConcreteStub::class);
        $this->assertFalse($container->isShared(ContainerConcreteStub::class));
    }

    public function testForgetInstancesForgetsAllInstances()
    {
        $container = new Container();
        $containerConcreteStub1 = new ContainerConcreteStub();
        $containerConcreteStub2 = new ContainerConcreteStub();
        $containerConcreteStub3 = new ContainerConcreteStub();
        $container->instance('Instance1', $containerConcreteStub1);
        $container->instance('Instance2', $containerConcreteStub2);
        $container->instance('Instance3', $containerConcreteStub3);
        $this->assertTrue($container->isShared('Instance1'));
        $this->assertTrue($container->isShared('Instance2'));
        $this->assertTrue($container->isShared('Instance3'));
        $container->forgetInstances();
        $this->assertFalse($container->isShared('Instance1'));
        $this->assertFalse($container->isShared('Instance2'));
        $this->assertFalse($container->isShared('Instance3'));
    }

    public function testContainerFlushFlushesAllBindingsAliasesAndResolvedInstances()
    {
        $container = new Container();
        $container->bind('ConcreteStub', function () {
            return new ContainerConcreteStub();
        }, true);
        $container->make('ConcreteStub');
        $this->assertArrayHasKey('ConcreteStub', $container->getBindings());
        $this->assertTrue($container->isShared('ConcreteStub'));
        $container->flush();
        $this->assertEmpty($container->getBindings());
        $this->assertFalse($container->isShared('ConcreteStub'));
    }

    public function testResolvingWithArrayOfParameters()
    {
        $container = new Container();
        $instance = $container->make(ContainerDefaultValueStub::class, ['default' => 'embers']);
        $this->assertSame('embers', $instance->default);

        $instance = $container->make(ContainerDefaultValueStub::class);
        $this->assertSame('foobar', $instance->default);

        $container->bind('foo', function ($app, $config) {
            return $config;
        });

        $this->assertEquals([1, 2, 3], $container->make('foo', [1, 2, 3]));
    }

    public function testResolvingWithUsingAnInterface()
    {
        $container = new Container();
        $container->bind(IContainerContractStub::class, ContainerInjectVariableStubWithInterfaceImplementation::class);
        $instance = $container->make(IContainerContractStub::class, ['something' => 'roach']);
        $this->assertSame('roach', $instance->something);
    }

    public function testNestedParameterOverride()
    {
        $container = new Container();
        $container->bind('foo', function ($app, $config) {
            return $app->make('bar', ['name' => 'ghost']);
        });
        $container->bind('bar', function ($app, $config) {
            return $config;
        });

        $this->assertEquals(['name' => 'ghost'], $container->make('foo', ['something']));
    }

    public function testNestedParametersAreResetForFreshMake()
    {
        $container = new Container();

        $container->bind('foo', function ($app, $config) {
            return $app->make('bar');
        });

        $container->bind('bar', function ($app, $config) {
            return $config;
        });

        $this->assertEquals([], $container->make('foo', ['something']));
    }

    public function testSingletonBindingsNotRespectedWithMakeParameters()
    {
        $container = new Container();

        $container->singleton('foo', function ($app, $config) {
            return $config;
        });

        $this->assertEquals(['name' => 'soap'], $container->make('foo', ['name' => 'soap']));
        $this->assertEquals(['name' => 'price'], $container->make('foo', ['name' => 'price']));
    }

    public function testCanBuildWithoutParameterStackWithNoConstructors()
    {
        $container = new Container();
        $this->assertInstanceOf(ContainerConcreteStub::class, $container->build(ContainerConcreteStub::class));
    }

    public function testCanBuildWithoutParameterStackWithConstructors()
    {
        $container = new Container();
        $container->bind(IContainerContractStub::class, ContainerImplementationStub::class);
        $this->assertInstanceOf(ContainerDependentStub::class, $container->build(ContainerDependentStub::class));
    }

    public function testContainerKnowsEntry()
    {
        $container = new Container();
        $container->bind(IContainerContractStub::class, ContainerImplementationStub::class);
        $this->assertTrue($container->has(IContainerContractStub::class));
    }

    public function testContainerCanBindAnyWord()
    {
        $container = new Container();
        $container->bind('ghost', stdClass::class);
        $this->assertInstanceOf(stdClass::class, $container->get('ghost'));
    }

    public function testContainerCanDynamicallySetService()
    {
        $container = new Container();
        $this->assertFalse(isset($container['name']));
        $container['name'] = 'ghost';
        $this->assertTrue(isset($container['name']));
        $this->assertSame('ghost', $container['name']);
    }

    public function testUnknownBindingEntryThrowsException()
    {
        $this->expectException(BindingNotFoundException::class);

        $container = new Container();
        $container->get('ghost');
    }

    public function testBoundEntriesThrowsContainerExceptionWhenNotResolvable()
    {
        $this->expectException(ContainerExceptionInterface::class);

        $container = new Container();
        $container->bind('ghost', IContainerContractStub::class);

        $container->get('ghost');
    }

    public function testContainerCanResolveClasses()
    {
        $container = new Container();
        $class = $container->get(ContainerConcreteStub::class);

        $this->assertInstanceOf(ContainerConcreteStub::class, $class);
    }
}
