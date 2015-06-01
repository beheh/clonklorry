<?php

namespace Lorry\Presenter\Auth;

use Lorry\Presenter;
use Lorry\Model\User;
use Lorry\Exception\AuthentificationFailedException;
use Lorry\Exception\FileNotFoundException;

class Callback extends Presenter
{

    public function get($providerName)
    {
        $this->session->ensureSession();
        if (isset($_SESSION['register_oauth'])) {
            unset($_SESSION['register_oauth']);
        }

        $oauth_provider = null;

        $uid = null;
        $nickname = null;
        $email = null;

        $returnto = null;
        if (isset($_SESSION['returnto'])) {
            $returnto = $_SESSION['returnto'];
            unset($_SESSION['returnto']);
        }

        $provider = 0;
        switch($providerName) {
            case 'github':
                $provider = User::PROVIDER_GITHUB;
            case 'google':
                $provider = User::PROVIDER_GOOGLE;
            case 'facebook':
                $provider = User::PROVIDER_FACEBOOK;
        }

        $this->logger->debug('handling authorization callback');
        try {
            switch ($provider) {
                case User::PROVIDER_GITHUB:
                    $provider_title = 'GitHub';
                    $oauth_provider = new \League\OAuth2\Client\Provider\Github(array(
                        'clientId' => $this->config->get('oauth/github/id'),
                        'clientSecret' => $this->config->get('oauth/github/secret'),
                        'redirectUri' => $this->config->get('base').'/auth/callback/github'
                    ));
                    break;
                case User::PROVIDER_GOOGLE:
                    $provider_title = 'Google';
                    $oauth_provider = new \League\OAuth2\Client\Provider\Google(array(
                        'clientId' => $this->config->get('oauth/google/id'),
                        'clientSecret' => $this->config->get('oauth/google/secret'),
                        'redirectUri' => $this->config->get('base').'/auth/callback/google'
                    ));
                    break;
                case User::PROVIDER_FACEBOOK:
                    $provider_title = 'Facebook';
                    $oauth_provider = new \League\OAuth2\Client\Provider\Facebook(array(
                        'clientId' => $this->config->get('oauth/facebook/id'),
                        'clientSecret' => $this->config->get('oauth/facebook/secret'),
                        'redirectUri' => $this->config->get('base').'/auth/callback/facebook'
                    ));
                    break;
                default:
                    throw new FileNotFoundException;
                    break;
            }

            if ($oauth_provider) {
                $this->logger->debug('authorization provider is "'.$provider_title.'"');
                if (!$this->session->verifyAuthorizationState(filter_input(INPUT_GET,
                            'state'))) {
                    throw new AuthentificationFailedException('invalid state (csrf?)');
                }

                if (isset($_GET['error'])) {
                    if (filter_input(INPUT_GET, 'error') === 'access_denied') {
                        if ($this->session->authenticated()) {
                            return $this->redirect('/settings#oauth');
                        } else {
                            return $this->redirect('/register');
                        }
                    }
                    throw new AuthentificationFailedException(filter_input(INPUT_GET,
                        'error'));
                }

                try {
                    // uppercase Authorization_Code: workaround for https://github.com/thephpleague/oauth2-client/issues/84
                   /* $token = $oauth_provider->getAccessToken('Authorization_Code',
                        array('code' => filter_input(INPUT_GET, 'code')));*/
                    $token = $oauth_provider->getAccessToken($grant = 'authorization_code', array('code' => filter_input(INPUT_GET, 'code')));
                } catch (\Exception $ex) {
                    throw new AuthentificationFailedException('could net get access token ('.$ex->getMessage().')');
                }
                if (!$token) {
                    throw new AuthentificationFailedException('missing access token');
                }
                $profile = $oauth_provider->getUserDetails($token);
                $uid = $profile->uid;
                $nickname = $profile->nickname;
                $email = $profile->email;
            }

            if (!$uid) {
                throw new AuthentificationFailedException('no user id provided');
            }
        } catch (AuthentificationFailedException $exception) {
            if ($this->session->authenticated()) {
                $this->logger->error($exception);
                return $this->redirect('/settings?update-oauth=failed#oauth');
            }
            throw $exception;
        }

        $this->logger->debug('authorization successful');
        if ($this->session->authenticated()) {
            // we ignore returnto
            unset($_SESSION['returnto']);

            // @todo test, if other user has already used this uid
            /*$users = $this->manager->getRepository('Lorry\Model\User');

                if ($test_user) {
                    $this->redirect('/settings?update-oauth=duplicate#oauth');
                    return;
                }
            }*/

            // we now trust provider and user
            $user = $this->session->getUser();
            $user->setOauth($provider, $uid);

            // we might be able to add the users github profile
            if($provider == 'github' && !$user->getGithubName()) {
                $user->setGithubName($profile->nickname);
            }

            $this->manager->flush();

            $this->redirect('/settings?update-oauth=success#oauth');
            return;
        } else {
            $users = $this->manager->getRepository('Lorry\Model\User');

            // grab user by oauth data
            $user = $users->findOneBy(array('oauth'.ucfirst($providerName) => $uid));

            if ($user instanceof User) {
                $url = '/';
                if ($returnto) {
                    $url = $returnto;
                }
                $this->session->start($user, false, false);
                $this->redirect($url.'#');
                return;
            } else {
                $user = $this->persistence->build('User')->byEmail($email);
                if ($user instanceof User) {
                    $this->redirect('/login?unknown-oauth&returnto=/settings#');
                    return;
                }

                // if no user matches register new user
                $_SESSION['register_oauth'] = array('provider' => $provider_title,
                    'uid' => $uid, 'username' => $nickname, 'email' => $email);
                $url = '/register';
                if ($returnto) {
                    $url .= '?returnto='.$returnto;
                }
                $this->redirect($url.'#');
                return;
            }
        }
    }
}
