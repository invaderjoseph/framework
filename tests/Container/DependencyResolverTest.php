<?php

namespace Emberfuse\Tests\Container;

use stdClass;
use ReflectionClass;
use ReflectionMethod;
use Emberfuse\Tests\TestCase;
use Emberfuse\Container\Container;
use Emberfuse\Container\DependencyResolver;
use Emberfuse\Tests\Container\Stubs\ContainerConcreteStub;
use Emberfuse\Tests\Container\Stubs\ContainerDefaultValueStub;

class DependencyResolverTest extends TestCase
{
    public function testResolveClassDependencies()
    {
        $resolver = $this->getResolver();
        $reflector = new ReflectionClass(ContainerDefaultValueStub::class);
        $method = $reflector->getConstructor();
        $dependencies = $method->getParameters();
        $parameters = $resolver->setParameterOverride([])->resolve($dependencies, []);

        $this->assertInstanceOf(ContainerConcreteStub::class, $parameters[0]);
        $this->assertEquals('foobar', $parameters[1]);
    }

    public function testResolveClassDependenciesOverride()
    {
        $resolver = $this->getResolver();
        $reflector = new ReflectionClass(ContainerDefaultValueStub::class);
        $method = $reflector->getConstructor();
        $dependencies = $method->getParameters();
        $parameters = $resolver->setParameterOverride(['default' => 'barbaz'])->resolve($dependencies);

        $this->assertInstanceOf(ContainerConcreteStub::class, $parameters[0]);
        $this->assertEquals('barbaz', $parameters[1]);
    }

    public function testResolveClassMethodDependencies()
    {
        $resolver = $this->getResolver();
        $mockClassInstance = new ContainerConcreteStub();
        $method = new ReflectionMethod($mockClassInstance, 'stubMethod');
        $dependencies = $method->getParameters();
        $parameters = $resolver->resolve($dependencies, []);
        $results = call_user_func_array([$mockClassInstance, 'stubMethod'], $parameters);

        $this->assertInstanceOf(stdClass::class, $parameters[0]);
        $this->assertInstanceOf(stdClass::class, $results[0]);
        $this->assertEquals('Thavarshan', $parameters[1]);
    }

    /**
     * Get instance of class method resolver class.
     *
     * @return \Emberfuse\Container\DependencyResolver
     */
    protected function getResolver(): DependencyResolver
    {
        return new DependencyResolver(new Container());
    }
}
