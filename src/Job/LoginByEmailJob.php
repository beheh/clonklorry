<?php

namespace Lorry\Job;

use Lorry\Email;

class LoginByEmailJob extends UserEmailJob
{

    public function getEmail()
    {
        return 'Login';
    }

    public function prepareEmail(Email $email, $args)
    {
        parent::prepareEmail($email, $args);
        $email->setLoginUrl($this->getLoginUrl());
    }

    public function getLoginUrl()
    {
        $user = $this->user;
        $expires = time() + 10 * 60;
        $hash = $this->security->signLogin($user, $expires, $user->getCounter(),
            true);
        $url = $this->config->get('base').'/login?username='.$user->getUsername().'&expires='.$expires.'&counter='.$user->getCounter().'&reset=1&hash='.$hash;
        return $url;
    }
}
