<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter\AbstractPresenter;

class Disabled extends AbstractPresenter
{

    public function get()
    {
        // don't localise here
        $notice = $this->config->get('notice/text');
        if ($notice) {
            $this->context['title'] = $notice;
        } else {
            $this->context['title'] = $this->config->get('brand').' is currently disabled';
            $this->context['description'] = 'Please come back later.';
        }
        $this->context['hide_greeter'] = true;
        $this->context['nobuttons'] = true;
        $this->display('generic/hero.twig');
    }
}
