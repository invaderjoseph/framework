<?php

namespace Emberfuse\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use Emberfuse\Support\Exceptions\BindingResolutionException as SupportBindingResolutionException;

class BindingResolutionException extends SupportBindingResolutionException implements ContainerExceptionInterface
{
}
