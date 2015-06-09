<?php

namespace Lorry\Validator;

use Lorry\Validator;

class TicketValidator extends Validator
{

    /**
     *
     * @param \Lorry\Model\Ticket $entity
     */
    public function performValidation($entity)
    {
        $this->validateSubject($entity->getSubject());
        $this->validateMessage($entity->getMessage());
    }

    public function validateSubject($subject)
    {
        $this->validateStringLength($subject, 3, 72, array(
            'tooShort' => gettext('Subject is too short.'),
            'tooLong' => gettext('Subject is too long.')
        ));
    }

    public function validateMessage($message)
    {
        $this->validateStringLength($message, 10, 2048, array(
            'tooShort' => gettext('Subject is too short.'),
            'tooLong' => gettext('Subject is too long.')
        ));
    }
}
