<?php

namespace Emberfuse\Tests\Support;

use stdClass;
use ReflectionClass;
use ReflectionMethod;
use Emberfuse\Tests\TestCase;
use Emberfuse\Container\Container;
use Emberfuse\Support\DependencyResolver;
use Emberfuse\Tests\Support\Stubs\MockConcreteClass;
use Emberfuse\Tests\Support\Stubs\MockConcreteClassWithMethod;

class DependencyResolverTest extends TestCase
{
    public function testResolveClassDependencies()
    {
        $resolver = $this->getResolver();
        $reflector = new ReflectionClass(MockConcreteClass::class);
        $method = $reflector->getConstructor();
        $dependencies = $method->getParameters();
        $parameters = $resolver->resolve($dependencies, []);

        $this->assertEquals('Thavarshan', $parameters[0]);
    }

    public function testResolveClassDependenciesOverride()
    {
        $resolver = $this->getResolver();
        $reflector = new ReflectionClass(MockConcreteClass::class);
        $method = $reflector->getConstructor();
        $dependencies = $method->getParameters();
        $parameters = $resolver->resolve($dependencies, ['name' => 'James']);

        $this->assertEquals('James', $parameters[0]);
    }

    public function testResolveClassMethodDependencies()
    {
        $resolver = $this->getResolver();
        $mockClassInstance = new MockConcreteClassWithMethod();
        $method = new ReflectionMethod($mockClassInstance, 'mockMethod');
        $dependencies = $method->getParameters();
        $parameters = $resolver->resolve($dependencies, []);
        $result = call_user_func_array([$mockClassInstance, 'mockMethod'], $parameters);

        $this->assertInstanceOf(stdClass::class, $parameters[0]);
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * Get instance of class method resolver class.
     *
     * @return \Emberfuse\Support\DependencyResolver
     */
    protected function getResolver(): DependencyResolver
    {
        return new DependencyResolver(new Container());
    }
}
