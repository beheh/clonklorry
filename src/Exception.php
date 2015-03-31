<?php

namespace Lorry;

use Exception as PHPException;

class Exception extends PHPException
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
