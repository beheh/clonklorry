<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter\AbstractPresenter;

class Front extends AbstractPresenter
{

    public function get()
    {
        $this->context['new_user'] = $this->session->getFlag('new_user'); // welcome new users with alternate sidebar

        /* Main banners */

        $bannerRepository = $this->manager->getRepository('Lorry\Model\Banner');
        $this->context['banners'] =  $bannerRepository->getTranslatedActiveBanners($this->localisation->getDisplayLanguage());

        /* New releases */

        $releaseRepository = $this->manager->getRepository('Lorry\Model\Release');
        $this->context['latest_releases'] = $releaseRepository->getLatestReleases();

        $this->display('site/front.twig');
    }
}
