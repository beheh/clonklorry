<?php

namespace Lorry\Validator;

use Lorry\Model\Banner;
use Lorry\Model\Image;

class BannerValidator extends AbstractValidator
{

    /**
     *
     * @param \Lorry\Model\Banner $entity
     */
    protected function performValidation($entity)
    {
        $this->validateStringLength($entity->getName(), 0, 64, gettext('Invalid name.'));
        $this->validateVisibility($entity->getVisibility());
        $this->validateTimeConstraint($entity->getShowFrom(), $entity->getShowUntil());
        if($entity->getDefaultUrl() !== null) {
            $this->validateUrl($entity->getDefaultUrl(), gettext('Default url is invalid.'));
        }
        if($entity->getDefaultImage() !== null) {
            $this->validateObject(Image::class, $entity->getDefaultImage(), gettext('Default image is invalid.'));
        }
    }

    protected function validateVisibility($visibility) {
        switch($visibility) {
            case Banner::VISIBILITY_HIDDEN:
            case Banner::VISIBILITY_PUBLIC:
                break;
            default:
                $this->fail(gettext('Invalid visibility.'));
        }
    }

    protected function validateTimeConstraint($from, $until)
    {
        if ((!is_null($from) && !is_object($from)) || (!is_null($until) && !is_object($until))) {
            $this->fail(gettext('Invalid timestamp.'));
        }

        if (is_object($from) && is_object($until) && $from > $until) {
            $this->fail(gettext('Invalid time range.'));
        }
    }
}
