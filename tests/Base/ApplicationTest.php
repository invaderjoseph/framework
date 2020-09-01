<?php

namespace Emberfuse\Tests\Base;

use Emberfuse\Base\Kernel;
use Psr\Log\LoggerInterface;
use Emberfuse\Tests\TestCase;
use Emberfuse\Base\Application;
use Monolog\Logger as MonologLogger;
use Psr\Container\ContainerInterface;

class ApplicationTest extends TestCase
{
    /**
     * Mock base application directory.
     *
     * @var string
     */
    protected static $folder = '/fixtures';

    public function testInstantiationAndBasePathRegistration()
    {
        $app = new Application(__DIR__ . static::$folder);

        $this->assertInstanceOf(Application::class, $app);
        $this->assertInstanceOf(ContainerInterface::class, $app);
        $this->assertEquals(__DIR__ . static::$folder, $app->make('path.base'));
    }

    public function testBasebindingsRegistration()
    {
        $app = new Application(__DIR__ . static::$folder);

        $this->assertInstanceOf(Application::class, $app->make('app'));
        $this->assertInstanceOf(ContainerInterface::class, $app->make('app'));
        $this->assertInstanceOf(Application::class, $app->make(Application::class));
        $this->assertInstanceOf(ContainerInterface::class, $app->make(Application::class));
    }

    public function testEnvironmentConfiguration()
    {
        $app = new Application(__DIR__ . static::$folder);
        $app['env'] = 'foo';

        $this->assertEquals('foo', $app->environment());
        $this->assertTrue($app->environment('foo'));
        $this->assertTrue($app->environment('f*'));
        $this->assertTrue($app->environment('foo', 'bar'));
        $this->assertTrue($app->environment(['foo', 'bar']));
        $this->assertFalse($app->environment('qux'));
        $this->assertFalse($app->environment('q*'));
        $this->assertFalse($app->environment('qux', 'bar'));
        $this->assertFalse($app->environment(['qux', 'bar']));
    }

    public function testAccessLoggerImplementation()
    {
        $app = new Application(__DIR__ . static::$folder);
        // Environment variables are required to implement the logger and so the application
        // must be bootstrapped in order to load the environment variables.
        $kernel = new Kernel($app);
        $kernel->bootstrapApplication();

        $this->assertInstanceOf(LoggerInterface::class, $app->getLogger());
        $this->assertInstanceOf(MonologLogger::class, $app->getLogger()->getMonolog());
    }
}
