<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\Model\User;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ModelValueInvalidException;

class Edit extends Presenter
{

    public function get($username)
    {
        $this->security->requireModerator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $user = $this->persistence->build('User')->byUsername($username);
        if (!$user) {
            throw new FileNotFoundException('user '.$username);
        }

        if ($user->isModerator() || $user->isAdministrator()) {
            $this->security->requireAdministrator();
        }

        $this->context['username'] = $user->getUsername();
        if (!isset($this->context['username_edit'])) {
            $this->context['username_edit'] = $user->getUsername();
        }
        if (isset($_GET['username-changed'])) {
            $this->success('username', gettext('Username was changed.'));
        }

        $this->context['email'] = isset($_POST['email']) ? filter_input(INPUT_POST,
                'email') : $user->getEmail();

        $this->context['self'] = $this->session->authenticated() && $user->getId()
            == $this->session->getUser()->getId();

        $this->context['administrator'] = $user->isAdministrator();
        $this->context['moderator'] = $user->isModerator();

        $this->display('user/edit.twig');
    }

    public function post($username)
    {
        $this->offerIdentification();
        $this->security->requireModerator();
        $this->security->requireValidState();

        $user = $this->persistence->build('User')->byUsername($username);
        if (!$user) {
            throw new FileNotFoundException('user '.$username);
        }

        if ($user->isModerator() || $user->isAdministrator()) {
            $this->security->requireAdministrator();
        }

        $new_username = trim(filter_input(INPUT_POST, 'username'));

        $errors = array();

        if (isset($_POST['change-username-submit']) && $username != $new_username) {
            $this->context['username_edit'] = $new_username;

            if ($this->persistence->build('User')->byUsername($new_username)) {
                $errors[] = gettext('Username already taken.');
            } else {
                try {
                    $user->setUsername($new_username);
                } catch (ModelValueInvalidException $e) {
                    $errors[] = sprintf(gettext('Username is %s.'),
                        $e->getMessage());
                }
            }

            if (empty($errors)) {
                if ($user->modified() && $user->save()) {
                    $this->redirect('/users/'.$new_username.'/edit?username-changed');
                    return;
                } else {
                    $this->error('username',
                        gettext('Username could not be changed.'));
                }
            } else {
                $this->error('username', implode('<br>', $errors));
            }
        }

        if (isset($_POST['permissions-submit'])) {
            $this->security->requireAdministrator();

            $permissions = filter_input(INPUT_POST, 'permissions');

            switch ($permissions) {
                case 'administrator':
                    $user->setPermission(User::PERMISSION_ADMINISTRATE);
                    break;
                case 'moderator':
                    $user->setPermission(User::PERMISSION_MODERATE);
                    break;
                case 'user':
                    $user->setPermission(User::PERMISSION_READ);
                    break;
                default:
                    $this->error('permissions',
                        sprintf(gettext('Permission is %s.'), gettext('invalid')));
                    break;
            }

            if ($user->modified()) {
                $user->save();

                if ($this->session->getUser()->getId() == $user->getId()) {
                    if ($user->isAdministrator()) {
                        $this->redirect('/users/'.$user->getUsername().'/edit');
                    } else {
                        $this->redirect('/users/'.$user->getUsername());
                    }
                    return;
                }

                $this->success('permissions', gettext('Permissions changed.'));
            }
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if (isset($_POST['change-contact-submit']) && $email != $user->getEmail()) {
            $this->context['email'] = $email;

            $errors = array();

            $previous_email = $user->getEmail();
            if ($this->persistence->build('User')->byEmail($email)) {
                $errors[] = gettext('Email address already used.');
            } else {
                try {
                    $user->setEmail($email);
                } catch (ModelValueInvalidException $e) {
                    $errors[] = sprintf(gettext('Email address is %s.'),
                        gettext('invalid'));
                }
            }

            if ($user->modified() && empty($errors)) {
                $user->save();

                $this->success('contact',
                        gettext('Contact details were changed.'));
            } else {
                $this->error('contact', implode('<br>', $errors));
            }
        }

        if(isset($_POST['password-reset-submit'])) {
            if($this->job->submit('LoginByEmail',
                        array('user' => $user->getId(), 'reset' => true))) {
                $this->success('reset', gettext('The user should receive an email shortly.'));
            }
            else {
                $this->error('reset', gettext('Error sending the email.'));
            }
        }

        $this->get($username);
    }
}
