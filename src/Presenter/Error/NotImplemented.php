<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter;

class NotImplemented extends Presenter\Error
{

    protected function getCode()
    {
        return 501;
    }

    protected function getMessage()
    {
        return 'Not Implemented';
    }

    protected function getLocalizedMessage()
    {
        return gettext('Not implemented');
    }

    protected function getLocalizedDescription()
    {
        return gettext('The method you requested has not been implemented.');
    }
}
