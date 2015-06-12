<?php

namespace Lorry\Exception;

class ForbiddenException extends LorryException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\Forbidden';
    }

    public function getApiType()
    {
        return 'forbidden';
    }

    public function getHttpCode()
    {
        return 403;
    }

    public function getHttpMessage()
    {
        return 'Forbidden';
    }
}
