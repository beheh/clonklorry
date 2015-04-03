<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\Exception\ModelValueInvalidException;
use Exception;

/*
 * @method \Lorry\Model\User byId(int $id)
 * @method \Lorry\Model\User[] byAnything()
 */

class User extends Model
{
    const PERMISSION_READ = 1;
    const PERMISSION_MODERATE = 2;
    const PERMISSION_ADMINISTRATE = 3;
    const FLAG_ALPHA = 1;
    const FLAG_BETA = 2;
    const FLAG_VIP = 4;
    const FLAG_CODER = 8;
    const FLAG_REPORTER = 16;

    public function getTable()
    {
        return 'user';
    }

    public function getSchema()
    {
        return array(
            'username' => 'string(3,16)',
            'secret' => 'string(255)',
            'password' => 'string(255)',
            'email' => 'string(255)',
            'registration' => 'datetime',
            'activated' => 'boolean',
            'clonkforge' => 'int',
            'github' => 'string',
            'language' => 'string(5,5)',
            'permissions' => 'int',
            'flags' => 'int',
            'counter' => 'int',
            'oauth_github' => 'string(255)',
            'oauth_google' => 'string(255)',
            'oauth_facebook' => 'string(255)');
    }

    public function setUsername($username)
    {
        $this->validateString($username, 3, 16);
        $this->validateRegexp($username, '/^[a-z0-9_]+$/i');
        $this->setValue('username', $username);
    }

    /**
     * @return \Lorry\Model\User
     */
    final public function byUsername($username)
    {
        return $this->byValue('username', $username);
    }

    public function getUsername()
    {
        return $this->getValue('username');
    }

    final public function setPassword($password)
    {
        $this->validateString($password, 8, 72);
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT,
                array('cost' => 12));
        } else {
            $hash = null;
        }
        $this->incrementCounter();
        $this->setValue('password', $hash);
    }

    final public function hasPassword()
    {
        return $this->getValue('password') !== null;
    }

    final public function matchPassword($password)
    {
        if (empty($password)) {
            return false;
        }
        return password_verify($password, $this->getValue('password')) === true;
    }

    public function setEmail($email)
    {
        $this->validateEmail($email);
        $this->setValue('email', $email);
        if (!$this->modified()) {
            return;
        }
    }

    public function getEmail()
    {
        return $this->getValue('email');
    }

    /**
     * @return \Lorry\Model\User
     */
    final public function byEmail($email)
    {
        return $this->byValue('email', $email);
    }

    public function setRegistration($registration)
    {
        return $this->setValue('registration', $registration);
    }

    public function getRegistration()
    {
        return $this->getValue('registration');
    }

    final public function isActivated()
    {
        return $this->getValue('activated');
    }

    final public function activate()
    {
        return $this->setValue('activated', true);
    }

    final public function deactivate()
    {
        return $this->setValue('activated', false);
    }

    final public function regenerateSecret()
    {
        $secret = base64_encode(openssl_random_pseudo_bytes(64));
        $this->incrementCounter();
        return $this->setValue('secret', $secret);
    }

    final public function getSecret()
    {
        return $this->getValue('secret');
    }

    final public function matchSecret($secret)
    {
        if (empty($secret)) {
            return false;
        }
        return hash_equals($this->getValue('secret'), $secret);
    }

    final public function setPermission($permission)
    {
        return $this->setValue('permissions', $permission);
    }

    final public function getPermissions()
    {
        return $this->getValue('permissions');
    }

    final public function byPermission($permission) {
        return $this->byValues(array('permissions' => array('>=', $permission)));
    }

    final public function hasPermission($permission)
    {
        return $this->getPermissions() >= $permission;
    }

    /**
     *
     * @return bool
     */
    final public function isAdministrator()
    {
        return $this->hasPermission(User::PERMISSION_ADMINISTRATE);
    }

    /**
     *
     * @return bool
     */
    final public function isModerator()
    {
        return $this->hasPermission(User::PERMISSION_MODERATE);
    }

    final public function setFlag($flag)
    {
        $flags = $this->getFlags();
        $flags = $flags | $flag;
        return $this->setFlags($flags);
    }

    final public function unsetFlag($flag)
    {
        $flags = $this->getFlags();
        $flags = $flags xor $flag;
        return $this->setFlags($flags);
    }

    final public function setFlags($flags)
    {
        return $this->setValue('flags', $flags);
    }

    final public function getFlags()
    {
        return $this->getValue('flags');
    }

    final public function hasFlag($flag)
    {
        return !!($this->getFlags() & $flag);
    }

    final public function getCounter()
    {
        return $this->getValue('counter');
    }

    final public function incrementCounter()
    {
        if (!$this->isLoaded()) {
            return true;
        }
        return $this->setValue('counter', $this->getValue('counter') + 1);
    }

    final public function verifyCounter($counter)
    {
        return $this->getValue('counter') <= $counter;
    }

    final public function setClonkforgeUrl($clonkforge)
    {
        $scanned = array();
        $id = null;
        if ($clonkforge) {
            $this->validateUrl($clonkforge);
            $clonkforge = preg_replace('|^(http://)?(www\.)?(.*)$|',
                'http://$3', $clonkforge);
            $scanned = sscanf($clonkforge, $this->config->get('clonkforge/url'));
            if (count($scanned) != 1 || empty($scanned[0])) {
                throw new ModelValueInvalidException(gettext('not a matching Clonk Forge URL'));
            }
            $id = $scanned[0];
        }
        try {
            return $this->setClonkforge($id);
        } catch (ModelValueInvalidException $e) {
            throw new ModelValueInvalidException(gettext('not a valid Clonk Forge URL'));
        }
    }

    final public function setClonkforge($clonkforge)
    {
        if ($clonkforge) {
            $this->validateNumber($clonkforge);
            if ($clonkforge < 1) {
                throw new ModelValueInvalidException(gettext('not a valid Clonk Forge profile id'));
            }
        } else {
            $clonkforge = null;
        }
        return $this->setValue('clonkforge', $clonkforge);
    }

    final public function getClonkforge()
    {
        return $this->getValue('clonkforge');
    }

    final public function getClonkforgeUrl()
    {
        $clonkforge = $this->getClonkforge();
        if ($clonkforge) {
            return sprintf($this->config->get('clonkforge/url'),
                $this->getClonkforge());
        }
        return '';
    }

    final public function setGithub($github)
    {
        if ($github) {
            $this->validateString($github, 1, 255);
            if (!preg_match('#^'.$this->config->get('github/name').'$#', $github)) {
                throw new ModelValueInvalidException(gettext('not a valid GitHub name'));
            }
        } else {
            $github = null;
        }
        return $this->setValue('github', $github);
    }

    final public function getGithub()
    {
        return $this->getValue('github');
    }

    final public function hasOauth($provider)
    {
        $this->ensureField('oauth_'.$provider);
        return $this->getValue('oauth_'.$provider) !== null;
    }

    /**
     * @return \Lorry\Model\User
     */
    final public function byOauth($provider, $uid)
    {
        $this->ensureField('oauth_'.$provider);
        return $this->byValue('oauth_'.$provider, $uid);
    }

    final public function setOauth($provider, $uid)
    {
        if (!$uid && !$this->hasPassword() && !$this->hasRemainingOauth($provider)) {
            // dissallow last oauth to be removed without a password
            throw new ModelValueInvalidException(gettext('the last remaining login method'));
        }
        if(!$this->isFieldValid('oauth_'.$provider)) {
            throw new ModelValueInvalidException(gettext('not a valid OAuth provider'));
        }
        $this->setValue('oauth_'.$provider, $uid);
    }

    final protected function hasRemainingOauth($except)
    {
        $providers = array('github', 'google', 'facebook');
        $provider_count = 0;
        foreach ($providers as $provider) {
            if ($provider == $except) {
                continue;
            }
            if ($this->getValue('oauth_'.$provider) !== null) {
                $provider_count++;
            }
        }
        return $provider_count > 0;
    }

    final public function setLanguage($language)
    {
        $this->validateLanguage($language);
        return $this->setValue('language', $language);
    }

    public function getProfileUrl()
    {
        return $this->config->get('base').'/users/'.$this->getUsername().'';
    }

    /**
     *
     * @return string
     */
    final public function getLanguage()
    {
        return $this->getValue('language');
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     *
     * @return array
     */
    public function forApi()
    {
        return array('name' => $this->getUsername(), 'administrator' => $this->isAdministrator(),
            'moderator' => $this->isModerator());
    }

    /**
     *
     * @return array
     */
    public function forPresenter()
    {
        return $this->forApi();
    }
}
