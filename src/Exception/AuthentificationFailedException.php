<?php

namespace Lorry\Exception;

class AuthentificationFailedException extends ConcreteException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\AuthFailed';
    }
}
