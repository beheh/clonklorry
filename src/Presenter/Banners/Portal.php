<?php

namespace Lorry\Presenter\Banners;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Model\Banner;

class Portal extends AbstractPresenter {

    public function get() {
        $this->security->requireAdministrator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $this->context['banners'] = $this->manager->getRepository('Lorry\Model\Banner')->findAll();

        $this->display('banners/portal.twig');
    }

    public function post() {
        $this->security->requireAdministrator();
        $this->security->requireIdentification();

        $banner = new Banner();
        $this->manager->persist($banner);
        $this->manager->flush();

        $this->redirect($this->config->get('base').'/banners/'.$banner->getId(), true);
    }
    
}