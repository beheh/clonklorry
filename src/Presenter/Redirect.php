<?php

namespace Lorry\Presenter;

use Lorry\Presenter\AbstractPresenter;

abstract class Redirect extends AbstractPresenter
{

    abstract public function getLocation();

    public function get()
    {
        $this->redirect($this->getLocation());
    }
}
