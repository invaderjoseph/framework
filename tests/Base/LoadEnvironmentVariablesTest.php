<?php

namespace Emberfuse\Tests\Base;

use Emberfuse\Tests\TestCase;
use Emberfuse\Base\Application;
use Emberfuse\Base\Bootstrappers\LoadEnvironmentVariables;

class LoadEnvironmentVariablesTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['FOO'], $_SERVER['FOO']);

        putenv('FOO');
    }

    public function testLoadEnvironmentVariablesFromBasePath()
    {
        $this->expectOutputString('');

        $app = new Application(__DIR__ . '/fixtures');
        (new LoadEnvironmentVariables())->bootstrap($app);

        $this->assertSame('bar', getenv('FOO'));
        $this->assertSame('bar', $_ENV['FOO']);
        $this->assertSame('bar', $_SERVER['FOO']);
    }
}
