<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\BadRequestException;
use Lorry\Exception\TooManyRequestsException;

class Login extends AbstractPresenter
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

        if (isset($_GET['hash'])) {
            $this->attemptTokenLogin();
            return;
        }

        if (isset($_GET['returnto'])) {
            $this->context['returnto'] = filter_input(INPUT_GET, 'returnto');
        }
        if (!isset($this->context['remember']) && !$this->session->getFlag('login_forget')) {
            $this->context['remember'] = true;
        }
        if (isset($_POST['email-submit']) || isset($_GET['forgot'])) {
            $this->context['reset_password'] = true;
            $this->context['email_visible'] = true;
            $this->context['email_focus'] = true;
        }
        if (isset($_GET['registered'])) {
            $this->context['registered'] = true;
            if (!isset($this->context['username'])) {
                $username = filter_input(INPUT_GET, 'registered');
                if (!empty($username)) {
                    $this->context['username_exists'] = true;
                }
                $this->context['username'] = $username;
            }
            if (!$this->hasAlert('login')) {
                $this->success('login',
                    gettext('Registration successful! We\'ll send you an email for you to activate your account.'));
            }
        }
        if (isset($_GET['activate'])) {
            if (!isset($this->context['username'])) {
                $username = filter_input(INPUT_GET, 'activate');
                if (!empty($username)) {
                    $this->context['username_exists'] = true;
                }
                $this->context['username'] = $username;
            }
            if (!$this->hasAlert('login')) {
                $this->warning('login',
                    gettext('Sign in to activate your account.'));
            }
        }
        if (isset($_GET['connect'])) {
            $this->context['connect'] = true;
            if (!$this->hasAlert('login')) {
                $this->warning('login', gettext('Sign in to use this service.'));
            }
        }
        if (isset($_GET['unknown-oauth']) && !$this->hasAlert('login')) {
            $this->warning('login',
                gettext('Sign in to link this login service to your account.'));
        }

        $this->display('account/login.twig');
    }

    public function post()
    {
        $flaps = $this->flaps;
        $flap = $flaps->getFlap('login');
        $flap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(3,
            '5s'));
        $flap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(10,
            '60s'));
        $flap->limit($_SERVER['REMOTE_ADDR']);

        $userRepository = $this->manager->getRepository('Lorry\Model\User');

        if (isset($_POST['email-submit'])) {
            // login by email token
            $this->context['email_focus'] = true;
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $user = $userRepository->findOneBy(array('email' => $email));
            if ($user) {
                
                $resetFlap = $flaps->getFlap('resetPassword');
                $resetFlap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(1, '60s'));
                $resetFlap->pushThrottlingStrategy(new \BehEh\Flaps\Throttling\LeakyBucketStrategy(5, '1h'));
                try {
                    $resetFlap->limit($user->getId());

                    if ($this->job->submit('ResetPassword', array('user_id' => $user->getId()))) {
                        $this->success('email', gettext('You should receive an email shortly.'));
                    } else {
                        $this->error('email', gettext('Error sending the email.'));
                    }
                }
                catch(TooManyRequestsException $ex) {
                    $this->error('email', gettext('You already requested an email a short while ago.'));
                }
            } else {
                // email is unknown
                $this->error('email', gettext('Email address unknown.'));
            }
            $this->context['email'] = $email;
        } else {
            // login by username and password
            $username = filter_input(INPUT_POST, 'username', FILTER_DEFAULT);
            $remember = filter_input(INPUT_POST, 'remember',
                    FILTER_VALIDATE_BOOLEAN) || false;
            // take username to next page
            $this->context['username'] = $username;
            // set remember checkmark to persist after post
            $this->context['remember'] = $remember;

            $user = $userRepository->findOneBy(array('username' => $username));
            if (!$user) {
                // try email address instead
               $user = $userRepository->findOneBy(array('email' => $username));
                if ($user) {
                    $this->context['email'] = $username;
                }
            }
            if ($user) {
                $this->context['username_exists'] = true;
                if ($user->matchPassword(filter_input(INPUT_POST, 'password',
                            FILTER_DEFAULT))) {
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
                    if (!empty($returnto)) {
                        $url = rawurldecode($returnto);
                    }
                    $this->redirect($url);
                    return;
                } else {
                    // password is incorrect
                    $this->error('login', gettext('Password wrong.'));
                }
            } else {
                // user does not exist
                $this->error('login',
                    gettext('Username or email address unknown.'));
            }
        }
        $this->get();
    }

    public function attemptTokenLogin()
    {
        $username = filter_input(INPUT_GET, 'username');
        $user = $this->manager->getRepository('Lorry\Model\User')->findOneBy(array('username' => $username));
        if (!$user) {
            throw new FileNotFoundException('user '.$username);
        }

        $expires = filter_input(INPUT_GET, 'expires');
        $counter = filter_input(INPUT_GET, 'counter');
        $reset = filter_input(INPUT_GET, 'reset', FILTER_VALIDATE_BOOLEAN);

        $hash = filter_input(INPUT_GET, 'hash');
        if (empty($hash)) {
            throw new BadRequestException();
        }

        try {
            $expected = $this->security->signLogin($user, $expires, $counter,
                $reset);
        } catch (\InvalidArgumentException $ex) {
            throw new BadRequestException();
        }

        if (hash_equals($expected, $hash) !== true) {
            throw new ForbiddenException('hash does not match expected value');
        }

        if ($expires < time()) {
            throw new ForbiddenException('token expired');
        }

        if ($counter < $user->getCounter()) {
            throw new ForbiddenException('counter is not current');
        }

        $user->incrementCounter();
        $this->manager->flush();

        $this->session->start($user, false, false);

        if ($reset) {
            $this->session->authorizeResetPassword();
            $this->redirect('/settings?change-password');
        }

        $this->redirect('/');
        return;
    }
}
