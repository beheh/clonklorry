<?php

namespace Lorry\Validator;

use Lorry\Model\BannerTranslation;

class BannerTranslationValidator extends AbstractValidator
{

    /**
     *
     * @param \Lorry\Model\BannerTranslation $entity
     */
    protected function performValidation($entity)
    {
        $this->validateStringLength($entity->getTitle(), 0, 64, gettext('Invalid title.'));
        $this->validateStringLength($entity->getTitle(), 0, 255, gettext('Invalid subtitle.'));
        if($entity->getUrl() !== null) {
            $this->validateUrl($entity->getUrl(), gettext('Url is invalid.'));
        }
        /*if($entity->getImage() !== null) {
            $this->validateObject(Image::class, $entity->getDefaultImage(), gettext('Default image is invalid.'));
        }*/
    }
}
