<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter\AbstractPresenter;
use Exception;

class InternalError extends AbstractPresenter implements ErrorPresenter
{

    protected function getCode()
    {
        return 500;
    }

    protected function getMessage()
    {
        return 'Internal Server Error';
    }

    protected function getLocalizedMessage()
    {
        return gettext('Internal server error');
    }

    protected function getLocalizedDescription()
    {
        return gettext('The server encountered an internal error processing the request.');
    }

    public function get(Exception $exception = null)
    {
        header('HTTP/1.1 '.$this->getCode().' '.$this->getMessage());

        $this->context['title'] = $this->getLocalizedMessage();
        $this->context['description'] = $this->getLocalizedDescription();

        if ($exception) {
            // only log on uncaught exceptions (otherwise we assume the user was informed about the error)
            if (get_class($this) == __CLASS__ || $this->config->get('debug')) {
                $this->logger->error($exception);
            } else {
                $this->logger->debug($exception);
            }
            if ($this->config->get('debug')) {
                $this->context['raw'] = '<pre>'.get_class($exception).': '.$exception->getMessage().'<br><br>'.$exception->getTraceAsString().'</pre>';
            }
        }
        $this->context['hide_greeter'] = true;

        $this->display('generic/hero.twig');
    }
}
