<?php

namespace Emberfuse\Tests\Base;

use Emberfuse\Tests\TestCase;
use Emberfuse\Base\Application;
use Emberfuse\Base\Bootstrap\LoadConfigurations;

class LoadConfigurationsTest extends TestCase
{
    public function testBootstrapConfigurations()
    {
        $app = new Application(__DIR__ . '/fixtures');
        $configLoader = new LoadConfigurations();
        $configLoader->bootstrap($app);

        $this->assertTrue($app->has('config'));
        $this->assertEquals(
            [
                'foo' => [
                    'bar' => 'baz',
                ],
            ],
            $app['config']->all()
        );
    }
}
