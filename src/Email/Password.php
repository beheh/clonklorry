<?php

namespace Lorry\Email;

class Password extends AbstractEmail
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
