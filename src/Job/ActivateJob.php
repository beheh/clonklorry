<?php

namespace Lorry\Job;

use Lorry\Email;

class ActivateJob extends UserEmailJob
{

    public function getEmail()
    {
        return 'Activate';
    }

    public function beforePerform()
    {
        if (isset($this->payload['address']) && $this->payload['address'] != $this->getRecipent()) {
            // user has since changed his address, no need to execute
            throw new \Exception('job does not need to executed anymore');
        }
    }

    public function prepareEmail(Email $email, $payload)
    {
        parent::prepareEmail($email, $payload);
        $email->setUrl($this->getActivationUrl());
    }

    public function getActivationUrl()
    {
        $user = $this->user;
        $expires = time() + 10 * 60;
        $address = $this->getRecipent();
        $hash = $this->security->signActivation($user, $expires, $address);
        $url = $this->config->get('base').'/users/'.$user->getUsername().'/activate?address='.urlencode($address).'&expires='.$expires.'&hash='.$hash;
        return $url;
    }
}
