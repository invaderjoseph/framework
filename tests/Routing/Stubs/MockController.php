<?php

namespace Emberfuse\Tests\Routing\Stubs;

use Emberfuse\Routing\Controller;

class MockController extends Controller
{
    /**
     * Show "bar" word.
     *
     * @return string
     */
    public function index(): string
    {
        return 'bar';
    }
}
