<?php

namespace Emberfuse\Tests\Support\Stubs;

class PipeTwo
{
    public function handle(array $data)
    {
        $data['bar'] = 'Chaged again';

        return $data;
    }
}
