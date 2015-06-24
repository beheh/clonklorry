<?php

namespace Lorry\Presenter\Banners;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\Validator\BannerTranslationValidator;

class Translation extends AbstractPresenter
{

    public function get($banner_id, $language_key)
    {
        $this->security->requireAdministrator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);
        if (!$banner) {
            throw new FileNotFoundException;
        }

        $translation = $banner->getTranslation($language_key);
        if (!$translation) {
            throw new FileNotFoundException;
        }

        if (!isset($this->context['translation_title'])) {
            $this->context['translation_title'] = $translation->getTitle();
        }

        if (!isset($this->context['subtitle'])) {
            $this->context['subtitle'] = $translation->getSubtitle();
        }

        if (!isset($this->context['url'])) {
            $this->context['url'] = $translation->getUrl();
        }

        $this->context['banner'] = $banner;
        $this->context['translation'] = $translation;

        $this->display('banners/translation.twig');
    }

    public function post($banner_id, $language_key)
    {
        $this->security->requireAdministrator();
        $this->security->requireIdentification();
        $this->security->requireValidState();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);
        if (!$banner) {
            throw new FileNotFoundException;
        }

        $translation = $banner->getTranslation($language_key);
        if (!$translation) {
            throw new FileNotFoundException;
        }

        $translationValidator = new BannerTranslationValidator();

        $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
        $this->context['translation_title'] = $title;
        $translation->setTitle($title);

        $subtitle = trim(filter_input(INPUT_POST, 'subtitle', FILTER_SANITIZE_STRING));
        $this->context['subtitle'] = $subtitle;
        $translation->setSubtitle($subtitle);

        if(filter_input(INPUT_POST, 'url_specified', FILTER_VALIDATE_BOOLEAN)) {
            $url = trim(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL));
            $this->context['url'] = $url;
        }
        else {
            $url = null;
        }
        $translation->setUrl($url);

        try {
            $translationValidator->validate($translation);
            $this->manager->flush();
            $this->success('translation-form', gettext('Banner saved.'));
        } catch (ValidationException $ex) {
            $this->manager->refresh($translation);
            $this->error('translation-form', implode('<br>', $ex->getFails()));
        };

        $this->get($banner_id, $language_key);
    }

}
