<?php

namespace Lorry\Exception;

class FileNotFoundException extends ConcreteException
{

    public function getPresenter()
    {
        return 'Lorry\Presenter\Error\FileNotFound';
    }

    public function getApiType()
    {
        return 'filenotfound';
    }

    public function getHttpCode()
    {
        return 404;
    }

    public function getHttpMessage()
    {
        return 'File Not Found';
    }
}
