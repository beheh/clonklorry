<?php

namespace Lorry\Presenter\Auth;

use ErrorException;
use Lorry\Presenter;
use Lorry\Exception\AuthentificationFailedException;
use Lorry\Exception\FileNotFoundException;

class Gateway extends Presenter
{

    public function get($provider)
    {
        try {
            $login_hint = false;
            if ($this->session->authenticated()) {
                $login_hint = $this->session->getUser()->getEmail();
            } else {
                $this->session->ensureSession();
                unset($_SESSION['returnto']);
                $returnto = filter_input(INPUT_GET, 'returnto');
                if ($returnto) {
                    $_SESSION['returnto'] = $returnto;
                }
            }
            switch ($provider) {
                case 'github':
                    $scopes = array();
                    if (!$this->session->authenticated()) {
                        $scopes[] = 'user:email';
                    }
                    $github = new \League\OAuth2\Client\Provider\Github(array(
                        'clientId' => $this->config->get('oauth/github/id'),
                        'clientSecret' => $this->config->get('oauth/github/secret'),
                        'redirectUri' => $this->config->get('base').'/auth/callback/github',
                        'scopes' => $scopes
                    ));
                    $authorizationUrl = $github->getAuthorizationUrl();
                    $this->session->setAuthorizationState($github->state);
                    $this->redirect($authorizationUrl, true);
                    break;
                case 'google':
                    $scopes = array('profile');
                    if (!$this->session->authenticated()) {
                        $scopes[] = 'email';
                    }
                    $google = new \League\OAuth2\Client\Provider\Google(array(
                        'clientId' => $this->config->get('oauth/google/id'),
                        'clientSecret' => $this->config->get('oauth/google/secret'),
                        'redirectUri' => $this->config->get('base').'/auth/callback/google',
                        'scopes' => $scopes
                    ));
                    $custom = '';
                    if ($login_hint) {
                        $custom .= '&login_hint='.$login_hint;
                    }
                    $authorizationUrl = $google->getAuthorizationUrl();
                    $this->session->setAuthorizationState($google->state);
                    $this->redirect($authorizationUrl, true);
                    break;
                case 'facebook':
                    $scopes = array('public_profile');
                    if (!$this->session->authenticated()) {
                        $scopes[] = 'email';
                    }
                    $facebook = new \League\OAuth2\Client\Provider\Facebook(array(
                        'clientId' => $this->config->get('oauth/facebook/id'),
                        'clientSecret' => $this->config->get('oauth/facebook/secret'),
                        'redirectUri' => $this->config->get('base').'/auth/callback/facebook',
                        'scopes' => $scopes
                    ));
                    $authorizationUrl = $facebook->getAuthorizationUrl();
                    $this->session->setAuthorizationState($facebook->state);
                    $this->redirect($authorizationUrl, true);
                    break;
                default:
                    throw new FileNotFoundException();
                    break;
            }
        } catch (AuthentificationFailedException $exception) {
            if ($this->session->authenticated()) {
                $this->logger->error($exception);
                return $this->redirect('/settings?update-oauth=failed#oauth');
            }
            throw $exception;
        }
    }
}
