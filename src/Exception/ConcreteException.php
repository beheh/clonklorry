<?php

namespace Lorry\Exception;

use \Exception as PHPException;

class ConcreteException extends PHPException implements Exception
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
