<?php

namespace Lorry\Email;

class Welcome extends Activate
{

    public function write()
    {
        $this->render('welcome.twig');
    }
}
