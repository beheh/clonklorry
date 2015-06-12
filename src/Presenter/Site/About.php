<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter\AbstractPresenter;

class About extends AbstractPresenter
{

    public function get()
    {
        $this->display('site/about.twig');
    }
}
