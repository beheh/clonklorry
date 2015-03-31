<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Api extends Presenter
{

    public function get()
    {
        $this->display('site/api.twig');
    }
}
