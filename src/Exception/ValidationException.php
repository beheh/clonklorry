<?php

namespace Lorry\Exception;

class ValidationException extends LorryException
{
    private $fails = array();

    public function setFails($fails) {
        $this->fails = $fails;
    }

    public function getFails() {
        return $this->fails;
    }
}
