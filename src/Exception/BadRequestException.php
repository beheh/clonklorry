<?php

namespace Lorry\Exception;

class BadRequestException extends ConcreteException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\BadRequest';
    }
}
