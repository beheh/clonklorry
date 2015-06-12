<?php

namespace Lorry\Exception;

class NotImplementedException extends LorryException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\NotImplemented';
    }
}
