<?php

namespace Lorry\Presenter\Redirect;

use Lorry\Presenter\AbstractPresenter;

class Front extends Presenter\Redirect
{

    public function getLocation()
    {
        return '/';
    }
}
