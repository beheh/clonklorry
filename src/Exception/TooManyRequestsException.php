<?php

namespace Lorry\Exception;

class TooManyRequestsException extends ConcreteException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\TooManyRequests';
    }
}
