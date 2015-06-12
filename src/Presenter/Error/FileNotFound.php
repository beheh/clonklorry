<?php

namespace Lorry\Presenter\Error;

class FileNotFound extends InternalError
{

    protected function getCode()
    {
        return 404;
    }

    protected function getMessage()
    {
        return 'File Not Found';
    }

    protected function getLocalizedMessage()
    {
        return gettext('File not found');
    }

    protected function getLocalizedDescription()
    {
        return gettext('The file you requested could not be found.');
    }
}
