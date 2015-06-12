<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter\AbstractPresenter;

class Privacy extends AbstractPresenter
{

    public function get()
    {
        $this->display('site/privacy.twig');
    }
}
