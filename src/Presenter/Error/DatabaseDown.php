<?php

namespace Lorry\Presenter\Error;

class DatabaseDown extends InternalError
{
    protected function getLocalizedMessage()
    {
        return gettext('Database down');
    }

    protected function getLocalizedDescription()
    {
        return gettext('The site could not reach its database.');
    }
}
