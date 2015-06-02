<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\Exception\ModelValueInvalidException;
use Lorry\Model\User;

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
                    $this->success('oauth',
                        gettext('Connected with login service.'));
                    break;
                case 'duplicate':
                    $this->error('oauth',
                        gettext('Login service is already linked to another account.'));
                    break;
                case 'failed':
                    $this->error('oauth',
                        gettext('Authentification with login service failed.'));
                    break;
            }
        }

        if (isset($_GET['remove-oauth'])) {
            $this->security->requireValidState();
            $provider = filter_input(INPUT_GET, 'remove-oauth');

            if ($provider) {
                try {
                    $user->setOauth($provider, null);
                    if ($user->modified()) {
                        $this->success('oauth',
                            gettext('Removed login service.'));
                        $this->manager->flush();
                    }
                } catch (ModelValueInvalidException $ex) {
                    $this->error('oauth',
                        sprintf(gettext('%s is %s.'), ucfirst($provider),
                            $ex->getMessage()));
                }
            }
        }

        $this->context['username'] = $user->getUsername();

        if (!isset($this->context['clonkforge'])) {
            $this->context['clonkforge'] = $user->getClonkforgeUrl();
        }
        if (!isset($this->context['github'])) {
            $this->context['github'] = $user->getGithubName();
        }

        $this->context['clonkforge_placeholder'] = sprintf($this->config->get('clonkforge/url'),
            0);
        $this->context['github_placeholder'] = $user->getUsername();

        if (!isset($this->context['email'])) {
            $this->context['email'] = $user->getEmail();
        }
        $this->context['activated'] = $user->isActivated();

        $this->context['language'] = $this->localisation->getDisplayLanguage();

        $this->context['password_exists'] = $user->hasPassword();
        $this->context['identified'] = $identified = $this->session->identified();
        if ((isset($_GET['add-password']) && !$user->hasPassword()) || (($this->session->canResetPassword())
            && isset($_GET['change-password']))) {
            $this->context['focus_password_new'] = true;
        }

        $this->context['can_reset_password'] = $this->session->canResetPassword();

        $oauth = array('github' => User::PROVIDER_GITHUB, 'google' => User::PROVIDER_GOOGLE, 'facebook' => User::PROVIDER_FACEBOOK);
        $this->context['oauth'] = array();
        foreach ($oauth as $name => $provider) {
            $this->context['oauth'][$name] = $user->hasOauth($provider);
        }

        $this->display('account/settings.twig');
    }

    public function post()
    {
        $this->security->requireLogin();
        $this->security->requireValidState();

        $user = $this->session->getUser();

        if (isset($_POST['profiles-form'])) {
            $errors = array();

            // Clonk Forge profile url
            $clonkforge = trim(filter_input(INPUT_POST, 'clonkforge', FILTER_VALIDATE_URL));
            $this->context['clonkforge'] = $clonkforge;
            try {
                $user->setClonkforgeUrl($clonkforge);
                $this->context['clonkforge'] = $user->getClonkforgeUrl();
            } catch (ModelValueInvalidException $e) {
                $errors[] = sprintf(gettext('Clonk Forge profile url is %s.'),
                        gettext('invalid'));
            }

            // GitHub name
            $github = trim(filter_input(INPUT_POST, 'github'));
            $this->context['github'] = $github;
            try {
                $user->setGithubName($github);
                $this->context['github'] = $user->getGithubName();
            } catch (ModelValueInvalidException $e) {
                $errors[] = sprintf(gettext('GitHub name is %s.'), $e->getMessage());
            }

            if(!empty($errors)) {
                $this->error('profiles', implode('<br>', $errors));
            }
            else if ($user->modified()) {
                $this->manager->flush();
                $this->success('profiles', gettext('Your links were saved.'));
            }
        }

        if (isset($_POST['contact-form'])) {
            $errors = array();

            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $this->context['email'] = $email;

            $previous_email = $user->getEmail();

            try {
                $user->setEmail($email);
            } catch (ModelValueInvalidException $e) {
                $errors[] = sprintf(gettext('Email address is %s.'),
                    gettext('invalid'));
            }

            if ($user->modified()) {
                if (empty($errors)) {
                    $this->manager->flush();

                    // remove activation jobs with previous address, if any
                    try {
                        $this->job->remove('Activate',
                            array('user' => $user->getId(), 'address' => $previous_email));
                    } catch (\Exception $ex) {
                    }
                    // submit new activation job
                    if ($user->isActivated()) {
                        $this->success('contact',
                            gettext('Contact details were changed.'));
                    } else {
                        $submitted = false;
                        try {
                            if ($this->job->submit('Activate',
                                    array('user' => $user->getId(), 'address' => $user->getEmail()))) {
                                $submitted = true;
                            }
                        } catch (\Exception $ex) {
                        }
                        if ($submitted) {
                            $this->success('contact',
                                gettext('Contact details were changed. Please remember to activate your account.'));
                        } else {
                            $this->warning('contact',
                                gettext('Contact details were changed, but we couldn\'t send you an email to activate your account. Pleasy try again later.'));
                        }
                    }
                } else {
                    $this->error('contact', implode('<br>', $errors));
                }
            } elseif (isset($_POST['resend'])) {
                $args = array('user' => $user->getId(), 'address' => $user->getEmail());
                // remove any previous activation jobs, if any
                $this->job->remove('Activate', $args);
                if ($this->job->submit('Activate', $args)) {
                    $this->success('contact',
                        gettext('You should receive the confirmation email soon.'));
                } else {
                    $this->alert('contact',
                        gettext('We can\'t send you a confirmation email right now. Try again later.'));
                }
            }
        }

        if (isset($_POST['language-form'])) {
            $language = filter_input(INPUT_POST, 'language');
            if ($this->localisation->setDisplayLanguage($language)) {
                $user->setLanguage($language);
                $this->manager->flush();
                $this->redirect('/settings');
                return;
            }
        }

        if (isset($_POST['remove-account-form'])) {
            $this->context['show_remove_account'] = true;

            if (filter_input(INPUT_POST, 'confirm', FILTER_VALIDATE_BOOLEAN)) {
                if (!$user->hasPassword() || $user->matchPassword(filter_input(INPUT_POST, 'password'))) {
                    $this->warning('remove-account', 'Not yet implemented.');
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
            $can_reset = $this->session->canResetPassword();
            if (!$has_password || $user->matchPassword($password_old) || $can_reset) {
                if ($password_new === $password_confirm) {
                    try {
                        $user->setPassword($password_new);
                        $this->session->clearResetPassword();
                        $this->manager->flush();
                        $this->session->identify();
                        $this->context['state'] = $this->session->regenerateState();
                        if ($has_password) {
                            $this->success('password',
                                gettext('Your password was changed.'));
                        } else {
                            $this->success('password',
                                gettext('Your password was set.'));
                        }
                    } catch (ModelValueInvalidException $ex) {
                        $this->error('password',
                            sprintf(gettext('Password is %s.'),
                                $ex->getMessage()));
                    }
                } else {
                    $this->context['focus_password'] = true;
                    $this->error('password', gettext('Passwords do not match.'));
                }
            } else {
                $this->context['focus_password'] = true;
                $this->error('password', gettext('Password wrong.'));
            }
        }

        if (isset($_POST['remote-logout-form'])) {
            $user->regenerateSecret();
            $this->manager->flush();
            $this->session->refresh();
            $this->context['state'] = $this->session->regenerateState();

            $this->success('remote-logout',
                gettext('All other devices were logged out.'));
        }

        $this->get();
    }
}
