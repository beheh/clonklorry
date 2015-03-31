<?php

namespace Lorry\Adapter;

use Lorry\Exception\TooManyRequestsException;
use BehEh\Flaps\ViolationHandlerInterface;

class LorryViolationHandler implements ViolationHandlerInterface
{

    public function handleViolation()
    {
        throw new TooManyRequestsException;
    }
}
