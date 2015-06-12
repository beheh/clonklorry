<?php

namespace Lorry;

use Lorry\Exception\TooManyRequestsException;
use BehEh\Flaps\ViolationHandlerInterface;

class RateLimitViolationHandler implements ViolationHandlerInterface
{

    public function handleViolation()
    {
        throw new TooManyRequestsException;
    }
}
