<?php

namespace Lorry\Exception;

class TooManyRequestsException extends LorryException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\TooManyRequests';
    }
}
