<?php

declare(strict_types=1);

namespace Emberfuse\Container\Exceptions;

use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

class BindingResolutionException extends RuntimeException implements ContainerExceptionInterface
{
}
