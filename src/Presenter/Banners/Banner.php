<?php

namespace Lorry\Presenter\Banners;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\Model\BannerTranslation;

class Banner extends AbstractPresenter {

    public function get($banner_id) {
        $this->security->requireAdministrator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);

        if(!$banner) {
            throw new FileNotFoundException;
        }

        $this->context['banner'] = $banner;

        $this->context['name'] = $banner->getName();

        $this->context['default_url'] = $banner->getDefaultUrl();
        $defaultImage = $banner->getDefaultImage();
        $this->context['default_image_guid'] = $defaultImage ? $defaultImage->getGuid() : '';

        $languages = $this->localisation->getAvailableLanguages();

        $this->context['translations'] = $banner->getTranslations()->toArray();

        $further_languages = array();
        foreach($languages as $language) {
            if($banner->getTranslation($language) === null) {
                $further_languages[] = $language;
            }
        }
        $this->context['further_languages'] = $further_languages;

        $this->display('banners/banner.twig');
    }

    public function post($banner_id) {
        $this->security->requireAdministrator();
        $this->security->requireIdentification();
        $this->security->requireValidState();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);

        if(!$banner) {
            throw new FileNotFoundException;
        }

        if(isset($_POST['banner-form'])) {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $banner->setName($name);
            $this->manager->flush();
        }

        if(isset($_POST['add-translation-form'])) {
            $language_key = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING);
            $language = $this->manager->getRepository('Lorry\Model\Language')->findOneBy(array('key' => $language_key));

            if($this->manager->getRepository('Lorry\Model\BannerTranslation')->findOneBy(array('banner' => $banner, 'language' => $language)) === null) {
                if($language !== null) {
                    $translation = new BannerTranslation();
                    $translation->setBanner($banner);
                    $translation->setLanguage($language);
                    $banner->addTranslation($translation);
                    $this->manager->flush();
                }
                else {
                    $this->error('add-translation', gettext('Invalid language.'));
                }
            }
        }

        $this->get($banner_id);
    }
    
}