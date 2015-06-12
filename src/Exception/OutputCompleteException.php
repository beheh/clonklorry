<?php

namespace Lorry\Exception;

class OutputCompleteException extends ConcreteException
{

    public function getPresenter()
    {
        return '';
    }
}
