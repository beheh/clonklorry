<?php

namespace Lorry\Exception;

class AuthentificationFailedException extends LorryException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\AuthFailed';
    }
}
