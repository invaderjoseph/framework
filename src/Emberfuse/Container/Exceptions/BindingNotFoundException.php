<?php

namespace Emberfuse\Container\Exceptions;

use RuntimeException;
use Psr\Container\NotFoundExceptionInterface;

class BindingNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
