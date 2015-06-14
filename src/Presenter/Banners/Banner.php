<?php

namespace Lorry\Presenter\Banners;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\Model\BannerTranslation;
use Lorry\Model\Banner as BannerModel;
use Lorry\Validator\BannerValidator;
use Lorry\Exception\ValidationException;
use \DateTime;

class Banner extends AbstractPresenter
{

    public function get($banner_id)
    {
        $this->security->requireAdministrator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);

        if (!$banner) {
            throw new FileNotFoundException;
        }

        $this->context['banner'] = $banner;

        if(!isset($this->context['name'])) {
            $this->context['name'] = $banner->getName();
        }

        $this->context['default_url'] = $banner->getDefaultUrl();
        $defaultImage = $banner->getDefaultImage();
        $this->context['default_image_guid'] = $defaultImage ? $defaultImage->getGuid() : '';

        $time = new DateTime('next friday 18:00');
        if ($banner->getShowFrom() !== null) {
            $this->context['show_from'] = $banner->getShowFrom()->format('Y-m-d\TH:i');
        } else {
            $this->context['show_immediately'] = true;
        }
        $this->context['show_from_placeholder'] = $time->format('Y-m-d\TH:i');

        $time->add(\DateInterval::createFromDateString('2 days 4 hours'));

        if ($banner->getShowUntil() !== null) {
            $this->context['show_until'] = $banner->getShowUntil()->format('Y-m-d\TH:i');
        } else {
            $this->context['show_forever'] = true;
        }
        $this->context['show_until_placeholder'] = $time->format('Y-m-d\TH:i');

        $this->context['enabled'] = $banner->getVisibility() === BannerModel::VISIBILITY_PUBLIC;

        $languages = $this->localisation->getAvailableLanguages();

        $this->context['translations'] = $banner->getTranslations()->toArray();

        $further_languages = array();
        foreach ($languages as $language) {
            if ($banner->getTranslation($language) === null) {
                $further_languages[] = $language;
            }
        }
        $this->context['further_languages'] = $further_languages;

        $this->display('banners/banner.twig');
    }

    public function post($banner_id)
    {
        $this->security->requireAdministrator();
        $this->security->requireIdentification();
        $this->security->requireValidState();

        $banner = $this->manager->getRepository('Lorry\Model\Banner')->find($banner_id);

        if (!$banner) {
            throw new FileNotFoundException;
        }

        if (isset($_POST['banner-form'])) {
            $bannerValidator = new BannerValidator();

            if (filter_input(INPUT_POST, 'name_specified', FILTER_VALIDATE_BOOLEAN)) {
                $banner->setName(trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING)));
            }
            else {
                 $banner->setName(null);
            }
            
            $defaultUrl = trim(filter_input(INPUT_POST, 'default_url', FILTER_SANITIZE_STRING));
            if(!empty($defaultUrl)) {
                var_dump($defaultUrl);
                $banner->setDefaultUrl($defaultUrl);
            }
            else {
                $banner->setDefaultUrl(null);
            }

            if (filter_input(INPUT_POST, 'show_from_specified', FILTER_VALIDATE_BOOLEAN)) {
                $showFromRaw = trim(filter_input(INPUT_POST, 'show_from', FILTER_SANITIZE_STRING));
                $showFrom = DateTime::createFromFormat('Y-m-d\TH:i', $showFromRaw);
                if (is_object($showFrom)) {
                    $banner->setShowFrom($showFrom);
                }
                else {
                    $bannerValidator->fail(gettext('Invalid Timestamp.'));
                }
            } else {
                $banner->setShowFrom(null);
            }

            if (filter_input(INPUT_POST, 'show_until_specified', FILTER_VALIDATE_BOOLEAN)) {
                $showUntilRaw = trim(filter_input(INPUT_POST, 'show_until', FILTER_SANITIZE_STRING));
                $showUntil = DateTime::createFromFormat('Y-m-d\TH:i', $showUntilRaw);
                if (is_object($showUntil)) {
                    $banner->setShowUntil($showUntil);
                }
                else {
                    $bannerValidator->fail(gettext('Timestamp is invalid.'));
                }
            } else {
                $banner->setShowUntil(null);
            }

            $banner->setVisibility(filter_input(INPUT_POST, 'banner_enable', FILTER_VALIDATE_BOOLEAN) ? BannerModel::VISIBILITY_PUBLIC : BannerModel::VISIBILITY_HIDDEN);

            try {
                $bannerValidator->validate($banner);
                $this->manager->flush();
                $this->success('banner-form', gettext('Banner saved.'));
            } catch (ValidationException $ex) {
                $this->manager->refresh($banner);
                $this->error('banner-form', implode('<br>', $ex->getFails()));
            }
        }

        if (isset($_POST['add-translation-form'])) {
            $language_key = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING);
            $language = $this->manager->getRepository('Lorry\Model\Language')->findOneBy(array('key' => $language_key));

            if ($this->manager->getRepository('Lorry\Model\BannerTranslation')->findOneBy(array('banner' => $banner, 'language' => $language)) === null) {
                if ($language !== null) {
                    $translation = new BannerTranslation();
                    $translation->setBanner($banner);
                    $translation->setLanguage($language);
                    $banner->addTranslation($translation);
                    $this->manager->flush();
                } else {
                    $this->error('add-translation', gettext('Invalid language.'));
                }
            }
        }

        $this->get($banner_id);
    }
}
