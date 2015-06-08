<?php

namespace Lorry\Email;

use Lorry\Email;

class Password extends Email
{

    public function write()
    {
        $this->render('password.twig');
    }

    public function setLoginUrl($url)
    {
        $this->context['reset_url'] = $url;
    }
}
