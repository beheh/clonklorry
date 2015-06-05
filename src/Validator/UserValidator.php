<?php

namespace Lorry\Validator;

use Lorry\Validator;

class UserValidator extends Validator
{

    /**
     *
     * @param \Lorry\Model\User $entity
     */
    public function performValidation($entity)
    {
        $this->validateUsername($entity->getUsername());
        $this->validateEmailAddress($entity->getEmail(), gettext('Email address is invalid'));
    }

    protected function validateUsername($username)
    {
        $this->validateStringLength($username, 3, 16,array(
            'tooShort' => gettext('Username too short'),
            'tooLong' => gettext('Username too long')));
        $this->validateStringPattern($username, '/^[a-zA-Z0-9_]+$/', gettext('Username is invalid'));
    }

}
