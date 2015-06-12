<?php

namespace Lorry\Presenter\Error;

class Forbidden extends InternalError
{

    protected function getCode()
    {
        return 403;
    }

    protected function getMessage()
    {
        return 'Forbidden';
    }

    protected function getLocalizedMessage()
    {
        return gettext('Forbidden');
    }

    protected function getLocalizedDescription()
    {
        return gettext('You do not have the permissions necessary to access this document.');
    }
}
