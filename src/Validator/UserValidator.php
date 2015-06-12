<?php

namespace Lorry\Validator;

class UserValidator extends AbstractValidator
{

    /**
     *
     * @param \Lorry\Model\User $entity
     */
    protected function performValidation($entity)
    {
        $this->validateUsername($entity->getUsername());
        $this->validateEmailAddress($entity->getEmail(), gettext('Email address is invalid.'));

        $this->validateNumber($entity->getLoginMethodCount(), 1, null, array(
            'noInt' => gettext('Login methods must a number.'),
            'tooSmall' => gettext('Last login method cannot be removed.'),
            'tooLarge' => gettext('Too many login methods.')
        ));

        $this->validateClonkforgeId($entity->getClonkforgeId());
        $this->validateGitHubName($entity->getGithubName());
    }

    protected function validateUsername($username)
    {
        $this->validateStringLength($username, 3, 16, array(
            'tooShort' => gettext('Username too short.'),
            'tooLong' => gettext('Username too long.')));
        $this->validateStringPattern($username, '/^[a-zA-Z0-9_]+$/', gettext('Username is invalid.'));
    }

    protected function validateClonkforgeId($clonkforgeId)
    {
        if ($clonkforgeId === null) {
            return;
        }
        $this->validateNumber($clonkforgeId, 1, null, array(
            'noInt' => gettext('Clonkforge id is not a number.'),
            'tooSmall' => gettext('Clonkforge id too small.'),
            'tooLarge' => gettext('Clonkforge id too large.')
        ));
    }

    protected function validateGitHubName($githubName)
    {
        if ($githubName === null) {
            return;
        }
        $this->validateStringPattern($githubName, '#^'.'([a-zA-Z0-9][a-zA-Z0-9-]*)'.'$#', gettext('GitHub name is invalid.'));
    }
}
