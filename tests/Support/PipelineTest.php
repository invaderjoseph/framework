<?php

namespace Emberfuse\Tests\Support;

use Emberfuse\Tests\TestCase;
use Emberfuse\Support\Pipeline;
use Emberfuse\Container\Container;
use Emberfuse\Tests\Support\Stubs\PipeOne;
use Emberfuse\Tests\Support\Stubs\PipeTwo;

class PipelineTest extends TestCase
{
    public function testBasicPipingProcess()
    {
        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $result = (new Pipeline(new Container()))
            ->send($data)
            ->through([PipeOne::class, PipeTwo::class])
            ->then(function ($data) {
                return $data;
            });

        $this->assertNotEquals($data, $result);
    }

    public function testSendingThroughEmptyPipes()
    {
        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $result = (new Pipeline(new Container()))
            ->send($data)
            ->through([])
            ->then(function ($data) {
                return $data;
            });

        $this->assertEquals($data, $result);
    }
}
