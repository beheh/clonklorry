<?php

namespace Lorry\Exception;

use Exception as PHPException;

class LorryException extends PHPException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error';
    }

    public function getApiType()
    {
        return 'internal';
    }

    public function getHttpCode()
    {
        return null;
    }

    public function getHttpMessage()
    {
        return null;
    }
}
