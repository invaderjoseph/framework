<?php

namespace Emberfuse\Routing\Validators;

use Emberfuse\Routing\Contracts\ValidatorInterface;

class UriValidator implements ValidatorInterface
{
    /**
     * Run validation against or using the provided arguments.
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function validate(...$arguments): bool
    {
        $requestPath = trim($arguments[1]->getPathInfo(), '/');

        $path = $requestPath === '/' ? '/' : '/' . $requestPath;

        return preg_match(
            $arguments[0]->getCompiled()->getRegex(),
            rawurldecode($path)
        );
    }
}
