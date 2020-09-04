<?php

namespace Emberfuse\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use Emberfuse\Support\Exceptions\DependencyResolutionException;

class BindingResolutionException extends DependencyResolutionException implements ContainerExceptionInterface
{
}
