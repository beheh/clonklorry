<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;

class Portal extends Presenter
{

    public function get()
    {
        $this->context['games'] = $this->manager->getRepository('Lorry\Model\Game')->findAll();

        $this->display('addon/portal.twig');
    }
}
