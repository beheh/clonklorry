<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter\AbstractPresenter;

class Logout extends AbstractPresenter
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
