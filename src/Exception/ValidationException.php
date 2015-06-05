<?php

namespace Lorry\Exception;

use Lorry\Exception;

class ValidationException extends Exception
{
    private $fails = array();

    public function setFails($fails) {
        $this->fails = $fails;
    }

    public function getFails() {
        return $this->fails;
    }
}
