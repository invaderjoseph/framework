<?php

namespace Emberfuse\Routing\Validators;

use Emberfuse\Routing\Contracts\ValidatorInterface;

class MethodValidator implements ValidatorInterface
{
    public function validate(...$arguments): bool
    {
        return $arguments[0]->method() === $arguments[1]->getMethod();
    }
}
