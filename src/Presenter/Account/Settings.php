<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\Model\User;
use Lorry\Validator\UserValidator;
use Lorry\Exception\ValidationException;
use Lorry\ModificationListener;

class Settings extends Presenter
{

    public function get()
    {
        $this->security->requireLogin();

        if (isset($_GET['oauth'])) {
            return $this->redirect($this->session->handleOauth());
        }

        $user = $this->session->getUser();

        if (isset($_GET['update-oauth'])) {
            switch (filter_input(INPUT_GET, 'update-oauth')) {
                case 'success':
                    $this->success('oauth', gettext('Connected with login service.'));
                    break;
                case 'duplicate':
                    $this->error('oauth', gettext('Login service is already linked to another account.'));
                    break;
                case 'failed':
                    $this->error('oauth', gettext('Authentification with login service failed.'));
                    break;
            }
        }

        if (isset($_GET['remove-oauth'])) {
            $this->security->requireValidState();
            $modificationListener = new ModificationListener($user);
            $userValidator = new UserValidator();
            $provider = filter_input(INPUT_GET, 'remove-oauth');

            if ($provider) {

                $user->setOauth($user->parseOauth($provider), null);

                try {
                    $userValidator->validate($user);
                    if ($modificationListener->isNotified()) {
                        $this->manager->flush();
                        $this->success('oauth', gettext('Removed login service.'));
                    }
                } catch (ValidationException $ex) {
                    $this->manager->refresh($user);
                    $this->error('oauth', implode('<br>', $ex->getFails()));
                }
            }
        }

        $this->context['username'] = $user->getUsername();

        if (!isset($this->context['clonkforge'])) {
            $this->context['clonkforge'] = sprintf($this->config->get('clonkforge/url'), urlencode($user->getClonkforgeId()));
        }
        if (!isset($this->context['github'])) {
            $this->context['github'] = $user->getGithubName();
        }

        $this->context['clonkforge_placeholder'] = sprintf($this->config->get('clonkforge/url'), 0);
        $this->context['github_placeholder'] = $user->getUsername();

        if (!isset($this->context['email'])) {
            $this->context['email'] = $user->getEmail();
        }
        $this->context['activated'] = $user->isActivated();

        $this->context['language'] = $this->localisation->getDisplayLanguage();

        $this->context['password_exists'] = $user->hasPassword();
        $this->context['identified'] = $identified = $this->session->identified();
        if ((isset($_GET['add-password']) && !$user->hasPassword()) || (($this->session->canResetPassword()) && isset($_GET['change-password']))) {
            $this->context['focus_password_new'] = true;
        }

        $this->context['can_reset_password'] = $this->session->canResetPassword();

        $this->context['oauth'] = $user->getOauthArray();

        $this->display('account/settings.twig');
    }

    public function post()
    {
        $this->security->requireLogin();
        $this->security->requireValidState();

        $user = $this->session->getUser();
        $userRepository = $this->manager->getRepository('Lorry\Model\User');
        $modificationListener = new ModificationListener($user);

        $userValidator = new UserValidator();

        if (isset($_POST['profiles-form'])) {
            // Clonk Forge profile url
            $clonkforgeUrl = trim(filter_input(INPUT_POST, 'clonkforge', FILTER_VALIDATE_URL));

            if (!empty($clonkforgeUrl)) {
                $clonkforgeUrl = preg_replace('|^(http://)?(www\.)?(.*)$|', 'http://$3', $clonkforgeUrl);

                $scanned = sscanf($clonkforgeUrl, $this->config->get('clonkforge/url'));
                if (count($scanned) != 1 || empty($scanned[0])) {
                    $userValidator->fail(gettext('Clonk Forge profile url is invalid.'));
                } else {
                    $user->setClonkforgeId($scanned[0]);
                }
            } else {
                $user->setClonkforgeId(null);
            }
            $this->context['clonkforge'] = $clonkforgeUrl;

            // GitHub name
            $githubName = trim(filter_input(INPUT_POST, 'github'));
            if (!empty($githubName)) {
                $this->context['github'] = $githubName;
                $user->setGithubName($githubName);
            } else {
                $user->setGithubName(null);
            }

            try {
                $userValidator->validate($user);
                if ($modificationListener->isNotified()) {
                    $this->manager->flush();
                    $this->success('profiles', gettext('Your links were saved.'));
                }
            } catch (ValidationException $ex) {
                $this->manager->refresh($user);
                $this->error('profiles', implode('<br>', $ex->getFails()));
            }
        }

        if (isset($_POST['contact-form'])) {
            $email = trim(filter_input(INPUT_POST, 'email'));
            $previousEmail = $user->getEmail();

            $user->setEmail($email);
            if ($email && $email !== $previousEmail && count($userRepository->findBy(array('email' => $email))) > 0) {
                $userValidator->fail('Email address is already in use.');
            }

            try {
                $userValidator->validate($user);
                if ($modificationListener->isNotified()) {
                    $this->manager->flush();
                    if ($user->isActivated()) {
                        $this->success('contact', gettext('Contact details were changed.'));
                    } else {
                        $this->warning('contact', gettext('Contact details were changed. Please still activate your account.'));
                        // @todo send activation email if not activated
                    }
                }
            } catch (ValidationException $ex) {
                $this->manager->refresh($user);
                $this->error('contact', implode('<br>', $ex->getFails()));
            }
        }

        if (isset($_POST['language-form'])) {
            $language = filter_input(INPUT_POST, 'language');
            if ($this->localisation->setDisplayLanguage($language)) {
                $user->setLanguage($language);
            } else {
                $userValidator->fail(gettext('Language is invalid.'));
            }
            try {
                $userValidator->validate($user);
                if ($modificationListener->isNotified()) {
                    $this->manager->flush();
                    $this->redirect('/settings');
                    return;
                }
            } catch (ValidationException $ex) {
                $this->manager->refresh($user);
                $this->error('language', implode('<br>', $ex->getFails()));
            }
        }

        if (isset($_POST['remove-account-form'])) {
            $this->context['show_remove_account'] = true;

            if (filter_input(INPUT_POST, 'confirm', FILTER_VALIDATE_BOOLEAN)) {
                if (!$user->hasPassword() || $user->matchPassword(filter_input(INPUT_POST, 'password'))) {
                    if ($user->isActivated()) {
                        $this->warning('remove-account', gettext('Okay. We\'ll send you one final email to confirm.'));
                        // @todo send removal confirmation
                    } else {
                        $this->manager->remove($user);
                        $this->manager->flush();
                        $this->session->end();
                        // goodbye, old friend
                        $this->redirect('/');
                        return;
                    }
                } else {
                    $this->error('remove-account', gettext('Password wrong.'));
                }
            } else {
                $this->error('remove-account', gettext('Confirmation required.'));
            }
        }

        if (isset($_POST['password-form'])) {
            $has_password = $user->hasPassword();
            $password_old = filter_input(INPUT_POST, 'password-old');
            $password_new = filter_input(INPUT_POST, 'password-new');
            $password_confirm = filter_input(INPUT_POST, 'password-confirm');

            if ($has_password && !$user->matchPassword($password_old) && !$this->session->canResetPassword()) {
                $userValidator->fail(gettext('Password wrong.'));
                $this->context['focus_password'] = true;
            }

            if ($password_new !== $password_confirm) {
                $userValidator->fail(gettext('New passwords do not match.'));
                $this->context['focus_password'] = true;
            }

            if (strlen($password_new) < 6) {
                $userValidator->fail(gettext('New password too short.'));
                $this->context['focus_password'] = true;
            }

            $user->setPassword($password_new);

            try {
                $userValidator->validate($user);
                $this->session->clearResetPassword();
                $this->manager->flush();

                $this->context['state'] = $this->session->regenerateState();
                $this->session->identify();

                if ($has_password) {
                    $this->success('password', gettext('Your password was changed.'));
                } else {
                    $this->success('password', gettext('Your password was set.'));
                }
            } catch (ValidationException $ex) {
                $this->manager->refresh($user);
                $this->error('password', implode('<br>', $ex->getFails()));
            }
        }

        if (isset($_POST['remote-logout-form'])) {
            $user->regenerateSecret();
            $this->manager->flush();

            $this->session->refresh();
            $this->context['state'] = $this->session->regenerateState();

            $this->success('remote-logout', gettext('All other devices were logged out.'));
        }

        $this->get();
    }
}
