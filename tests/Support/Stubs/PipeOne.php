<?php

namespace Emberfuse\Tests\Support\Stubs;

class PipeOne
{
    public function handle(array $data)
    {
        $data['foo'] = 'Chaged';

        return $data;
    }
}
