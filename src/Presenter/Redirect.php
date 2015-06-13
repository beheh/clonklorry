<?php

namespace Lorry\Presenter;

use Lorry\Presenter\AbstractPresenter;

abstract class RedirectPresenter extends AbstractPresenter
{

    abstract public function getLocation();

    public function get()
    {
        $this->redirect($this->getLocation());
    }
}
