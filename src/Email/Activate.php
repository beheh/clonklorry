<?php

namespace Lorry\Email;

use Lorry\Email;

class Activate extends Email
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
