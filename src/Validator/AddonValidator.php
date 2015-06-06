<?php

namespace Lorry\Validator;

use Lorry\Validator;

class AddonValidator extends Validator
{

    /**
     *
     * @param \Lorry\Model\Addon $entity
     */
    public function performValidation($entity)
    {
        $this->validateTranslations($entity->getTranslations());
    }

    protected function validateTranslations($translations)
    {
        if ($translations->count($translations) < 1) {
            $this->fail(gettext('Addon must have at least one translation.'));
        }
    }
}
