<?php

namespace Lorry\Presenter;

use Lorry\Presenter;

abstract class Redirect extends Presenter
{

    abstract public function getLocation();

    public function get()
    {
        $this->redirect($this->getLocation());
    }
}
