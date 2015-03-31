<?php

namespace Lorry\Exception;

use Lorry\Exception;

class TooManyRequestsException extends Exception
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\TooManyRequests';
    }
}
