<?php

namespace Lorry\Presenter\Redirect;

use Lorry\Presenter\RedirectPresenter;

class Front extends RedirectPresenter
{

    public function getLocation()
    {
        return '/';
    }
}
