<?php

namespace Emberfuse\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class BindingResolutionException extends DependencyResolutionException implements ContainerExceptionInterface
{
}
