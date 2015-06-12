<?php

namespace Lorry\Exception;

class BadRequestException extends LorryException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\BadRequest';
    }
}
