<?php

namespace Emberfuse\Routing\Validators;

use Emberfuse\Routing\Contracts\ValidatorInterface;

class HostValidator implements ValidatorInterface
{
    public function validate(...$arguments): bool
    {
        if (is_null($arguments[0]->getCompiled()->getHostRegex())) {
            return true;
        }

        return preg_match(
            $arguments[0]->getCompiled()->getHostRegex(),
            $arguments[1]->getHost()
        );
    }
}
