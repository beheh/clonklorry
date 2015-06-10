<?php

namespace Lorry\Job;

use Lorry\Email;
use RuntimeException;

abstract class UserEmailJob extends EmailJob
{
    /**
     * @var \Lorry\Model\User;
     */
    protected $user;

    public function getRecipent()
    {
        return $this->user->getEmail();
    }

    public function prepareEmail(Email $email, $args)
    {
        if(!isset($args['user_id'])) {
            throw new RuntimeException('required arg "user_id" is not set');
        }
        $userId = intval($args['user_id']);
        $this->user = $this->manager->getRepository('Lorry\Model\User')->find($userId);
        if(!$this->user) {
            throw new RuntimeException('couldn\'t find a user with id '.$userId);
        }
        $this->localisation->silentLocalize($this->user->getLanguage());
        $email->setUser($this->user);
        parent::prepareEmail($email, $args);
    }
}
