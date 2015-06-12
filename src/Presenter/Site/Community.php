<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter\AbstractPresenter;

class Community extends AbstractPresenter
{

    public function get()
    {
        $this->display('site/community.twig');
    }
}
