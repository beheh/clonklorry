<?php

namespace Lorry\Validator;

use Lorry\Validator;

class AddonTranslationValidator extends Validator
{

    /**
     *
     * @param \Lorry\Model\AddonTranslation $entity
     */
    public function performValidation($entity)
    {
        $this->validateTitle($entity->getTitle());
    }

    protected function validateTitle($title)
    {
        $this->validateStringLength($title, 4, 64, array(
            'tooShort' => gettext('Title is too short.'),
            'tooLong' => gettext('Title is too long.'),
        ));
        $this->validateStringPattern($title, '/^[\w\s:.]*$/', gettext('Title is invalid.'));
    }
}
