<?php

namespace Lorry\Exception;

class NotImplementedException extends ConcreteException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\NotImplemented';
    }
}
