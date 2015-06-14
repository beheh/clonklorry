<?php

namespace Lorry\Presenter\Banners;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;

class Translation extends AbstractPresenter {

    public function get($banner_id, $language_key) {
        $this->security->requireAdministrator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);

        if(!$banner) {
            throw new FileNotFoundException;
        }

        $translation = $banner->getTranslation($language_key);

        if(!$translation) {
            throw new FileNotFoundException();
        }

        $this->context['banner'] = $banner;
        $this->context['translation'] = $translation;

        $this->display('banners/translation.twig');
    }
    
}