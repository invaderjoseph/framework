<?php

namespace Emberfuse\Tests\Base;

use Mockery;
use Emberfuse\Base\Kernel;
use Emberfuse\Base\Logger;
use Psr\Log\LoggerInterface;
use Emberfuse\Tests\TestCase;
use Emberfuse\Base\Application;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger as MonologLogger;

class LoggerTest extends TestCase
{
    /**
     * Instance of logger.
     *
     * @var \Emberfuse\Base\Logger
     */
    protected $logger;

    protected function setUp(): void
    {
        $app = new Application(__DIR__ . '/fixtures');
        $app['env'] = 'testing';

        $this->logger = new Logger(new MonologLogger($app->environment()));
    }

    public function tearDown(): void
    {
        Mockery::close();

        $logFile = __DIR__ . '/fixtures/logs/app.log';

        if (file_exists($logFile)) {
            unlink($logFile);
        }
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->logger);
    }

    public function testCreateMonologInstance()
    {
        $this->assertInstanceOf(MonologLogger::class, $this->logger->getMonolog());
        $this->assertEquals('testing', $this->logger->getMonolog()->getName());
    }

    public function testErrorLogHandlerCanBeCreated()
    {
        $logger = new Logger($monolog = Mockery::mock(MonologLogger::class));
        $monolog->shouldReceive('pushHandler')->once()->with(Mockery::type(ErrorLogHandler::class));
        $logger->useErrorLog();
    }

    public function testMethodsPassErrorAdditionsToMonolog()
    {
        $logger = new Logger($monolog = Mockery::mock(MonologLogger::class));
        $monolog->shouldReceive('error')->once()->with('foo', []);

        $logger->error('foo');
    }

    public function testWriteLogsToFile()
    {
        $app = new Application(__DIR__ . '/fixtures');
        $kernel = new Kernel($app);
        $kernel->bootstrapApplication();

        $app->getLogger()->info('Test log');

        $this->assertTrue(file_exists(__DIR__ . '/fixtures/logs/app.log'));
    }
}
