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

    /**
     * Show "post bar" words.
     *
     * @return string
     */
    public function store(): string
    {
        return 'post bar';
    }

    /**
     * Show "$name" parameter content.
     *
     * @param string $name
     *
     * @return string
     */
    public function show(string $name): string
    {
        return $name;
    }

    /**
     * Show "$name, $age" parameters content.
     *
     * @param string $name
     * @param int    $age
     *
     * @return string
     */
    public function showWithOptions(string $name, int $age = 25): string
    {
        return $name . $age;
    }

    /**
     * Show "$name, $age, $location" parameters content.
     *
     * @param string $name
     * @param int    $age
     * @param string $location
     *
     * @return string
     */
    public function showWithThreeOptions(string $name, int $age = 25, string $location = 'SL'): string
    {
        return $name . $age . $location;
    }

    /**
     * Show "$name, $age" parameters content.
     *
     * @param string $name
     * @param int    $age
     *
     * @return string
     */
    public function showParams(string $name, int $age = 25): string
    {
        return $name . $age;
    }

    /**
     * Show "$age" parameter content.
     *
     * @param int $age
     *
     * @return string
     */
    public function showOptionalParams(int $age = 25): string
    {
        return $age;
    }

    /**
     * Show "$name, $age" parameters content.
     *
     * @param string $name
     * @param int    $age
     *
     * @return string
     */
    public function showTwoOptionalParams(string $name = 'thavarshan', int $age = 25): string
    {
        return $name . $age;
    }

    /**
     * Show "hello" word.
     *
     * @return string
     */
    public function hello(): string
    {
        return 'hello';
    }
}
