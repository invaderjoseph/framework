<?php

namespace Emberfuse\Http;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

class Request extends BaseRequest implements RequestInterface
{
}
