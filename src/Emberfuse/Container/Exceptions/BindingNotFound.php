<?php

declare(strict_types=1);

namespace Emberfuse\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class BindingNotFound extends Exception implements NotFoundExceptionInterface
{
}
