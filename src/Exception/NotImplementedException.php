<?php

namespace Lorry\Exception;

use Lorry\Exception;

class NotImplementedException extends Exception
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\NotImplemented';
    }
}
