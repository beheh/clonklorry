<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter\AbstractPresenter;

class Identify extends AbstractPresenter
{

    public function get()
    {
        if (!$this->session->authenticated()) {
            $this->redirect('/login');
            return;
        }

        $this->security->requireLogin();
        $this->offerIdentification();

        $this->context['hide_greeter'] = true;

        if ($this->session->identified()) {
            $this->redirect('/');
        }
    }

    public function post()
    {
        $return = filter_input(INPUT_POST, 'return');
        if (!$return) {
            $return = '/';
        }

        if (!$this->session->authenticated()) {
            // redirect user to login
            $this->redirect('/login?returnto='.$return);
            return;
        }

        $this->security->requireLogin();
        $this->security->requireValidState();

        $user = $this->session->getUser();
        if (!$this->session->identified() && $user->hasPassword() && isset($_POST['password'])) {
            if ($user->matchPassword(filter_input(INPUT_POST, 'password'))) {
                $this->session->identify();
            }
        }

        if ($this->session->identified()) {
            $this->redirect($return);
        }

        $this->get();
    }
}
