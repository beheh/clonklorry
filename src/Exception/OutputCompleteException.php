<?php

namespace Lorry\Exception;

class OutputCompleteException extends LorryException
{

    public function getPresenter()
    {
        return '';
    }
}
