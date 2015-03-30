<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Login extends Presenter
{

    /**
     * @Inject
     * @var \BehEh\Flaps\Flaps
     */
    private $flaps;

    public function get()
    {
        if (isset($_SESSION['register_oauth'])) {
            unset($_SESSION['register_oauth']);
        }

        if ($this->session->authenticated()) {
            $this->redirect('/');
            return;
        }

        if (isset($_GET['oauth'])) {
            return $this->redirect($this->session->handleOauth());
        }

        if (isset($_GET['returnto'])) {
            $this->context['returnto'] = filter_input(INPUT_GET, 'returnto');
        }
        if (!isset($this->context['remember']) && !$this->session->getFlag('login_forget')) {
            $this->context['remember'] = true;
        }
        if (isset($_POST['email_submit']) || $this->session->getFlag('login_email')) {
            $this->context['email_visible'] = true;
        }
        if (isset($_POST['reset_password']) || isset($_GET['forgot'])) {
            $this->context['reset_password'] = true;
            $this->context['email_visible'] = true;
            $this->context['email_focus'] = true;
        }
        if (isset($_GET['registered'])) {
            $this->context['registered'] = true;
            if(!isset($this->context['username'])) {
                $username = filter_input(INPUT_GET, 'registered');
                if (!empty($username)) {
                    $this->context['username_exists'] = true;
                }
                $this->context['username'] = $username;
            }
            if (!$this->hasAlert('login')) {
                $this->success('login', gettext('Registration successful! We\'ll send you an email for you to activate your account.'));
            }
        }
        if (isset($_GET['connect'])) {
            $this->context['connect'] = true;
            if (!$this->hasAlert('login')) {
                $this->warning('login', gettext('Sign in to use this service.'));
            }
        }
        if (isset($_GET['unknown-oauth']) && !$this->hasAlert('login')) {
            $this->warning('login', gettext('Sign in to link this login service to your account.'));
        }

        $this->display('account/login.twig');
    }

    public function post()
    {
        $flaps = $this->flaps;
        $flap = $flaps->getFlap('login');
        $flap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(3, '5s'));
        $flap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(10, '60s'));
        $flap->limit($_SERVER['REMOTE_ADDR']);

        if (isset($_POST['email-submit'])) {
            // login by email token
            $this->context['email_focus'] = true;
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $user = $this->persistence->build('User')->byEmail($email);
            $reset = filter_input(INPUT_POST, 'reset_password', FILTER_VALIDATE_BOOLEAN) || false;
            if ($user) {
                try {
                    $this->job->submit('LoginByEmail', array('user' => $user->getId(), 'reset' => $reset));
                    $this->success('email', gettext('You should receive a link shortly.'));
                    if (!$reset) {
                        // show email by default in future
                        $this->session->setFlag('login_email');
                    }
                } catch (\Exception $ex) {
                    $this->error('email', gettext('Login via email failed.'));
                }
            } else {
                // email is unknown
                $this->error('email', gettext('Email address unknown.'));
            }
            $this->context['email'] = $email;
        } else {
            // login by username and password
            $username = filter_input(INPUT_POST, 'username', FILTER_DEFAULT);
            $remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOLEAN) || false;
            // take username to next page
            $this->context['username'] = $username;
            // set remember checkmark to persist after post
            $this->context['remember'] = $remember;
            $user = $this->persistence->build('User')->byUsername($username);
            if (!$user) {
                // try email address instead
                $user = $this->persistence->build('User')->byEmail($username);
            }
            if ($user) {
                $this->context['username_exists'] = true;
                if ($user->matchPassword(filter_input(INPUT_POST, 'password', FILTER_DEFAULT))) {
                    // do not show email login by default
                    $this->session->unsetFlag('login_email');
                    // log user in
                    $this->session->start($user, $remember, true);
                    if (!$remember) {
                        $this->session->setFlag('login_forget');
                    } else {
                        $this->session->unsetFlag('login_forget');
                    }
                    $url = '/';
                    $returnto = filter_input(INPUT_GET, 'returnto');
                    if ($returnto) {
                        $url = $returnto;
                    }
                    $this->redirect($url);
                    return;
                } else {
                    // password is incorrect
                    $this->error('login', gettext('Password wrong.'));
                }
            } else {
                // user does not exist
                $this->error('login', gettext('Username or email address unknown.'));
            }
        }
        $this->get();
    }

}
