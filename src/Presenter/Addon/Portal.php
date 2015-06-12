<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter\AbstractPresenter;

class Portal extends AbstractPresenter
{

    public function get()
    {
        $this->context['games'] = $this->manager->getRepository('Lorry\Model\Game')->findAll();

        $this->display('addon/portal.twig');
    }
}
