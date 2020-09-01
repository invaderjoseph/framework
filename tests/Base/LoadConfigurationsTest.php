<?php

namespace Emberfuse\Tests\Base;

use Emberfuse\Tests\TestCase;
use Emberfuse\Base\Application;
use Symfony\Component\Yaml\Yaml;
use Emberfuse\Support\Repository;
use Emberfuse\Base\Bootstrap\LoadConfigurations;

class LoadConfigurationsTest extends TestCase
{
    public function testGetConfigurationFiles()
    {
        $app = new Application(__DIR__ . '/fixtures');
        $configLoader = new LoadConfigurations($app);

        $this->assertEquals(
            [
              'test' => __DIR__ . '/fixtures/config/test.yaml',
            ],
            $this->setAccessibleMethod($configLoader, 'getConfigurationFiles', [$app])
        );
    }

    public function testParseConfigurationFiles()
    {
        $app = new Application(__DIR__ . '/fixtures');
        $config = new Repository([]);
        $configLoader = new LoadConfigurations($app);
        $configFile = $this->setAccessibleMethod($configLoader, 'getConfigurationFiles', [$app]);

        $this->assertEquals(
            [
                'foo' => [
                    'bar' => 'baz',
                ],
            ],
            Yaml::parseFile($configFile['test'])
        );
        $config->set('test', Yaml::parseFile($configFile['test']));
        $this->assertEquals(
            [
                'foo' => [
                    'bar' => 'baz',
                ],
            ],
            $config->get('test')
        );
    }
}
