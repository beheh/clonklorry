<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter\AbstractPresenter;

class Api extends AbstractPresenter
{

    public function get()
    {
        $this->display('site/api.twig');
    }
}
