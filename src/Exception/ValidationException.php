<?php

namespace Lorry\Exception;

class ValidationException extends ConcreteException
{
    private $fails = array();

    public function setFails($fails) {
        $this->fails = $fails;
    }

    public function getFails() {
        return $this->fails;
    }
}
