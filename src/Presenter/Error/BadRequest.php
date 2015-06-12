<?php

namespace Lorry\Presenter\Error;

class BadRequest extends InternalError
{

    protected function getCode()
    {
        return 400;
    }

    protected function getMessage()
    {
        return 'Bad Request';
    }

    protected function getLocalizedMessage()
    {
        return gettext('Bad Request');
    }

    protected function getLocalizedDescription()
    {
        return gettext('The request was invalid. It may be malformed or missing required parameters.');
    }
}
