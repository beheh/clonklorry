<?php

namespace Lorry\Job;

use Lorry\Email;

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
        $this->user = $this->manager->getRepository('Lorry\Model\User')->find($args['user']);
        $this->localisation->silentLocalize($this->user->getLanguage());
        $email->setUser($this->user);
        parent::prepareEmail($email, $args);
    }
}
