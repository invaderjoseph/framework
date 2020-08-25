<?php

namespace Emberfuse\Routing\Validators;

use Emberfuse\Routing\Contracts\ValidatorInterface;

class UriValidator implements ValidatorInterface
{
    public function validate(...$arguments): bool
    {
        $path = $arguments[1]->getPathInfo() === '/'
            ? '/'
            : '/' . $arguments[1]->getPathInfo();

        return preg_match(
            $arguments[0]->getCompiled()->getRegex(),
            rawurldecode($path)
        );
    }
}
