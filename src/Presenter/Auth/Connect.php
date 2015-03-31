<?php

namespace Lorry\Presenter\Auth;

use Lorry\Presenter;

class Connect extends Presenter
{

    public function get()
    {
        header('Expires: 0');

        if (!$this->session->authenticated()) {
            $this->redirect('/login?connect&returnto=/connect');
            return;
        }

        $this->security->requireLogin();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $this->display('account/connect.twig');
    }
}
