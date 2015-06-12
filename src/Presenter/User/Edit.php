<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter\AbstractPresenter;
use Lorry\Model\User;
use Lorry\Exception\FileNotFoundException;
use Lorry\Validator\UserValidator;
use Lorry\ModificationListener;
use Lorry\Exception\ValidationException;

class Edit extends AbstractPresenter
{

    public function get($username)
    {
        $this->security->requireModerator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $user = $this->manager->getRepository('Lorry\Model\User')->findOneBy(array('username' => $username));

        if (!$user) {
            throw new FileNotFoundException('unknown user "'.$username.'"');
        }

        if ($user->getUsername() !== $username) {
            $this->redirect($this->config->get('base').'/users/'.$user->getUsername().'/edit', true);
            return;
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

        $this->context['moderations'] = $user->getModerations();

        $this->context['email'] = isset($_POST['email']) ? filter_input(INPUT_POST, 'email') : $user->getEmail();

        $this->context['self'] = $this->session->authenticated() && $user->getId() == $this->session->getUser()->getId();

        $this->context['administrator'] = $user->isAdministrator();
        $this->context['moderator'] = $user->isModerator();

        $this->display('user/edit.twig');
    }

    public function post($username)
    {
        $this->offerIdentification();
        $this->security->requireModerator();
        $this->security->requireValidState();

        $self = $this->session->getUser();

        $userRepository = $this->manager->getRepository('Lorry\Model\User');
        $user = $userRepository->findOneBy(array('username' => $username));

        if (!$user) {
            throw new FileNotFoundException('unknown user "'.$username.'"');
        }

        if ($user->isModerator() || $user->isAdministrator()) {
            $this->security->requireAdministrator();
        }

        $userValidator = new UserValidator();
        $modificationListener = new ModificationListener($user);

        if (isset($_POST['change-username-submit'])) {
            $oldUsername = $user->getUsername();
            $newUsername = trim(filter_input(INPUT_POST, 'username'));

            $this->context['username_edit'] = $newUsername;

            $existingUser = $userRepository->findOneBy(array('username' => $newUsername));
            if ($existingUser && $existingUser != $user) {
                $userValidator->fail(gettext('Username already taken.'));
            }

            $user->setUsername($newUsername);

            try {
                $userValidator->validate($user);
                if ($modificationListener->isNotified()) {
                    $this->security->trackUserModeration($user, $self, 'changeUsername', $oldUsername, $newUsername);
                    $this->manager->flush();
                    $this->redirect('/users/'.$newUsername.'/edit?username-changed');
                    return;
                }
            } catch (ValidationException $ex) {
                $this->manager->refresh($user);
                $this->error('username', implode('<br>', $ex->getFails()));
            }
        }

        if (isset($_POST['change-contact-submit']) && !$self) {
            $oldEmail = $user->getEmail();
            $newEmail = trim(filter_input(INPUT_POST, 'email'));

            $this->context['email'] = $newEmail;

            $existingUser = $userRepository->findOneBy(array('email' => $newEmail));
            if ($existingUser && $existingUser != $user) {
                $userValidator->fail(gettext('Email address already in use.'));
            }

            $user->setEmail($newEmail);

            try {
                $userValidator->validate($user);
                if ($modificationListener->isNotified()) {
                    $this->security->trackUserModeration($user, $self, 'changeEmail', $oldEmail, $newEmail);
                    $this->manager->flush();
                    $this->success('contact', gettext('Contact details were changed.'));
                }
            } catch (ValidationException $ex) {
                $this->manager->refresh($user);
                $this->error('contact', implode('<br>', $ex->getFails()));
            }
        }


        if (isset($_POST['permissions-submit'])) {
            $this->security->requireAdministrator();

            $previous_permissions = $user->isAdministrator() ? 'administrator' : ($user->isModerator() ? 'moderator' : 'user');
            $permissions = filter_input(INPUT_POST, 'permissions');

            switch ($permissions) {
                case 'administrator':
                    $user->setPermissions(User::PERMISSION_ADMINISTRATE);
                    break;
                case 'moderator':
                    $user->setPermissions(User::PERMISSION_MODERATE);
                    break;
                case 'user':
                    $user->setPermissions(User::PERMISSION_READ);
                    break;
                default:
                    $userValidator->fail(gettext('Permission is invalid.'));
                    break;
            }

            try {
                $userValidator->validate($user);
                if ($modificationListener->isNotified()) {
                    $this->security->trackUserModeration($user, $self, 'changePermissions', $previous_permissions, $permissions);
                    $this->manager->flush();

                    // redirect to profile if self and not administrator anymore
                    if ($this->session->getUser()->getId() == $user->getId()) {
                        if (!$user->isAdministrator()) {
                            $this->redirect('/users/'.$user->getUsername());
                            return;
                        }
                    }

                    $this->success('permissions', gettext('Permissions changed.'));
                }
            } catch (ValidationException $ex) {
                $this->manager->refresh($user);
                $this->error('permissions', implode('<br>', $ex->getFails()));
            }
        }

        if (isset($_POST['password-reset-submit']) && !$self) {
            if ($this->job->submit('LoginByEmail', array('user' => $user->getId(), 'reset' => true))) {
                $this->security->trackUserModeration($user, 'password_reset', null, null, $self);

                $this->success('reset', gettext('The user should receive an email shortly.'));
            } else {
                $this->error('reset', gettext('Error sending the email.'));
            }
        }

        $this->get($username);
    }
}
