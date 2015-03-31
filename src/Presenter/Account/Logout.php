<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Logout extends Presenter
{

    public function get()
    {
        if ($this->session->authenticated()) {
            $this->session->unsetFlag('new_user', false);
            $this->session->logout();
        }
        return $this->redirect('/');
    }
}
