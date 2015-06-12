<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter\AbstractPresenter;

class Download extends AbstractPresenter
{

    public function get($gamename, $addonname, $version = 'latest')
    {
        echo 'download for '.$gamename.'/'.$addonname.'-'.$version;
    }
}
