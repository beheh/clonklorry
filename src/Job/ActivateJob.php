<?php

namespace Lorry\Job;

use Lorry\Email;

class ActivateJob extends UserEmailJob
{

    public function getEmail()
    {
        return 'Activate';
    }

    public function prepareEmail(Email $email, $payload)
    {
        parent::prepareEmail($email, $payload);
        $email->setUrl($this->getActivationUrl());
    }

    public function getActivationUrl()
    {
        $user = $this->user;
        $expires = time() + 7 * 24 * 60 * 60;
        $address = $this->getRecipent();
        $hash = $this->security->signActivation($user, $expires);
        $url = $this->config->get('base').'/users/'.$user->getUsername().'/activate?expires='.$expires.'&hash='.$hash;
        return $url;
    }
}
