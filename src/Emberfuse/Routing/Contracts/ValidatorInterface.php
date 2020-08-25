<?php

namespace Emberfuse\Routing\Contracts;

interface ValidatorInterface
{
    /**
     * Run validation against or using the provided arguments.
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function validate(...$arguments): bool;
}
