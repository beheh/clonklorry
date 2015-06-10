<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\Exception\ForbiddenException;
use Lorry\Model\User;
use Lorry\Validator\UserValidator;
use Lorry\Exception\ValidationException;

class Register extends Presenter
{

    public function get()
    {
        if ($this->session->authenticated()) {
            return $this->redirect('/');
        }

        if (isset($_GET['oauth'])) {
            return $this->redirect($this->session->handleOauth(true));
        }

        if (isset($_GET['returnto'])) {
            $this->context['returnto'] = filter_input(INPUT_GET, 'returnto');
        }

        if (isset($_GET['cancel'])) {
            unset($_SESSION['register_oauth']);
        }

        $this->context['oauth'] = false;
        if (isset($_SESSION['register_oauth'])) {
            $register = $_SESSION['register_oauth'];

            if ($register['username'] && !isset($this->context['username'])) {
                $this->context['username'] = $register['username'];
            }
            if ($register['email'] && !isset($this->context['email'])) {
                $this->context['email'] = $register['email'];
            }
            $this->context['provider'] = $register['provider'];

            $this->context['oauth'] = true;
        }

        if (!$this->config->get('enable/registration')) {
            $this->context['registration_disabled'] = true;
        }

        $this->display('account/register.twig');
    }

    public function post()
    {
        if (!$this->config->get('enable/registration')) {
            throw new ForbiddenException('registration is disabled');
        }

        $username = filter_input(INPUT_POST, 'username');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password');
        $repeatedPassword = filter_input(INPUT_POST, 'password-repeat');

        $this->context['username'] = $username;
        $this->context['email'] = $email;

        $oauth = false;
        if (isset($_SESSION['register_oauth']) && filter_input(INPUT_POST, 'use-oauth')) {
            $oauth = $_SESSION['register_oauth'];
        }

        $user = new User();
        $userRepository = $this->manager->getRepository('Lorry\Model\User');
        $userValidator = new UserValidator();

        $user->setUsername($username);
        if (count($userRepository->findBy(array('username' => $username))) > 0) {
            $userValidator->fail(gettext('Username is already taken.'));
        }

        $user->setEmail($email);
        if ($email && count($userRepository->findBy(array('email' => $email))) > 0) {
            $userValidator->fail(gettext('Email address is already in use.'));
        }

        if ($oauth) {
            $user->setOauth(strtolower($oauth['provider']), $oauth['uid']);
        } else {
            $user->setPassword($password);
            if ($password !== $repeatedPassword) {
                $userValidator->fail(gettext('Passwords do not match.'));
            } else if(strlen($password) < 6) {
                $userValidator->fail(gettext('Password too short.'));
            }
        }

        $user->setLanguage($this->localisation->getDisplayLanguage());

        try {
            $userValidator->validate($user);
            $this->manager->persist($user);
            $this->manager->flush();
            $this->session->setFlag('new_user', false);
            $this->job->submit('Welcome', array('user_id' => $user->getId()));
            // return user to previous page
            $returnto = filter_input(INPUT_GET, 'returnto');
            if ($oauth) {
                $url = '/';
                if ($returnto) {
                    $url = $returnto;
                }
                $this->session->start($user, false, false);
                $this->redirect($url);
                return;
            } else {
                $url = '/login?registered='.$user->getUsername();
                if ($returnto) {
                    $url .= '&returnto='.$returnto;
                }
                $this->redirect($url);
                return;
            }
        } catch (ValidationException $ex) {
            $this->error('register', implode('<br>', $ex->getFails()));
        }

        $this->get();
    }

}
