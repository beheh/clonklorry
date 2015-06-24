<?php

namespace Lorry\Presenter\Error;

class AuthFailed extends InternalError
{

    protected function getCode()
    {
        return 500;
    }

    protected function getLocalizedMessage()
    {
        return gettext('Authentification failed');
    }

    protected function getLocalizedDescription()
    {
        return gettext('The authentification with the login service failed.');
    }
}
