<?php

namespace Lorry\Email;

class Activate extends AbstractEmail
{

    public function write()
    {
        $this->render('activate.twig');
    }

    public function setUrl($url)
    {
        $this->context['activation_url'] = $url;
    }
}
