<?php

namespace Lorry\Presenter\Error;

class TooManyRequests extends InternalError
{

    protected function getCode()
    {
        return 429;
    }

    protected function getMessage()
    {
        return 'Too Many Requests';
    }

    protected function getLocalizedMessage()
    {
        return gettext('Too many requests');
    }

    protected function getLocalizedDescription()
    {
        return gettext('You have exceeded the allowed number of requests.');
    }
}
