<?php

namespace Lorry\Model;

use Lorry\Exception\ModelValueInvalidException;
use Lorry\Model2;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 */
class User extends Model2
{
    /** @Column(type="string", length=16, unique=true) */
    protected $username;

    /** @Column(type="string", length=255, unique=true) */
    protected $email;

    /* Authentication */

    /** @Column(type="string", nullable=true) */
    protected $secret;

    /** @Column(type="string", name="password_hash", nullable=true) */
    protected $passwordHash;

    /** @Column(type="integer") */
    protected $counter = 0;

    /** @Column(type="string", name="oauth_github", unique=true, nullable=true) */
    protected $oauthGithub;

    /** @Column(type="string", name="oauth_google", unique=true, nullable=true) */
    protected $oauthGoogle;

    /** @Column(type="string", name="oauth_facebook", unique=true, nullable=true) */
    protected $oauthFacebook;

    /* State */

    /** @Column(type="datetime") */
    protected $registration;

    /** @Column(type="datetime", nullable=true) */
    protected $activation;

    /* Settings */

    /** @Column(type="string") */
    protected $language;

    /** @Column(type="integer", name="clonkforge_id", nullable=true) */
    protected $clonkforgeId;

    /** @Column(type="string", name="github_name", nullable=true) */
    protected $githubName;

    /* Attributes */

    /** @Column(type="integer") */
    protected $permissions = 1;

    /** @Column(type="integer") */
    protected $flags = 0;

    /* Data */
    /**
     * @OneToMany(targetEntity="Addon", mappedBy="owner")
     * @var Addon[]
     * */
    //protected $ownedAddons = null;

    /* Consts */

    const PERMISSION_READ = 1;
    const PERMISSION_MODERATE = 2;
    const PERMISSION_ADMINISTRATE = 3;
    const FLAG_ALPHA = 1;
    const FLAG_BETA = 2;
    const FLAG_VIP = 4;
    const FLAG_CODER = 8;
    const FLAG_REPORTER = 16;
    const PROVIDER_GITHUB = 1;
    const PROVIDER_GOOGLE = 1;
    const PROVIDER_FACEBOOK = 1;

    /* Getters/Setters */

    final public function setUsername($username)
    {
        //$this->validateString($username, 3, 16);
        //$this->validateRegexp($username, '/^[a-z0-9_]+$/i');
        $this->username = $username;
    }

    final public function getUsername()
    {
        return $this->username;
    }

    final public function setPassword($password)
    {
        //$this->validateString($password, 8, 72);
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT,
                array('cost' => 12));
        } else {
            $hash = null;
        }
        $this->incrementCounter();
        $this->passwordHash = $hash;
    }

    final public function hasPassword()
    {
        return $this->passwordHash !== null;
    }

    final public function matchPassword($password)
    {
        if (empty($password)) {
            return false;
        }
        return password_verify($password, $this->passwordHash) === true;
    }

    public function setEmail($email)
    {
        //$this->validateEmail($email);
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @PrePersist
     */
    public function register()
    {
        $this->registration = new \DateTime();
    }

    public function getRegistration()
    {
        return $this->registration;
    }

    final public function isActivated()
    {
        return $this->activation !== null;
    }

    final public function activate()
    {
        return $this->activation = new \DateTime();
    }

    final public function deactivate()
    {
        return $this->activation = null;
    }

    final public function regenerateSecret()
    {
        $secret = base64_encode(openssl_random_pseudo_bytes(64));
        $this->secret = $secret;
        $this->incrementCounter();
    }

    final public function getSecret()
    {
        return $this->secret;
    }

    final public function matchSecret($secret)
    {
        if (empty($secret) || empty($this->secret)) {
            return false;
        }
        return hash_equals($this->secret, $secret);
    }

    final public function setPermission($permission)
    {
        return $this->permissions = $permission;
    }

    final public function getPermissions()
    {
        return $this->permissions;
    }

    final public function hasPermission($permission)
    {
        return $this->getPermissions() >= $permission;
    }

    final public function isAdministrator()
    {
        return $this->hasPermission(self::PERMISSION_ADMINISTRATE);
    }

    final public function isModerator()
    {
        return $this->hasPermission(self::PERMISSION_MODERATE);
    }

    final public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    final public function getFlags()
    {
        return $this->flags;
    }

    final public function setFlag($flag)
    {
        $flags = $this->getFlags();
        $flags = $flags | $flag;
        $this->setFlags($flags);
    }

    final public function unsetFlag($flag)
    {
        $flags = $this->getFlags();
        $flags = $flags xor $flag;
        $this->setFlags($flags);
    }

    final public function hasFlag($flag)
    {
        return !!($this->getFlags() & $flag);
    }

    final public function getCounter()
    {
        return $this->counter;
    }

    final public function incrementCounter()
    {
        return $this->counter++;
    }

    final public function verifyCounter($counter)
    {
        return $this->counter <= $counter;
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
            $this->setClonkforgeId($id);
        } catch (ModelValueInvalidException $e) {
            throw new ModelValueInvalidException(gettext('not a valid Clonk Forge URL'));
        }
    }

    final public function setClonkforgeId($id)
    {
        if ($id) {
            $this->validateNumber($id);
            if ($id < 1) {
                throw new ModelValueInvalidException(gettext('not a valid Clonk Forge profile id'));
            }
        } else {
            $id = null;
        }
        $this->clonkforgeId = $id;
    }

    final public function getClonkforge()
    {
        return $this->clonkforgeId;
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

    final public function setGithubName($name)
    {
        if ($name) {
            //validate to pattern ([a-zA-Z0-9][a-zA-Z0-9-]*)
            //$this->validateString($name, 1, 255);
            if (!preg_match('#^'.'([a-zA-Z0-9][a-zA-Z0-9-]*)'.'$#', $name)) {
                throw new ModelValueInvalidException(gettext('not a valid GitHub name'));
            }
        } else {
            $name = null;
        }
        return $this->githubName = $name;
    }

    final public function getGithubName()
    {
        return $this->githubName;
    }

    final public function hasOauth($provider)
    {
        switch ($provider) {
            case self::PROVIDER_GITHUB:
                return $this->oauthGithub !== null;
            case self::PROVIDER_GOOGLE:
                return $this->oauthGoogle !== null;
            case self::PROVIDER_FACEBOOK:
                return $this->oauthFacebook !== null;
        }
        return false;
    }

    final public function setOauth($provider, $uid)
    {
        if (!$uid && !$this->hasPassword() && !$this->hasRemainingOauth($provider)) {
            // do not allow last oauth to be removed without a password
            throw new ModelValueInvalidException(gettext('the last remaining login method'));
        }
        switch ($provider) {
            case self::PROVIDER_GITHUB:
                $this->oauthGithub = $uid;
                break;
            case self::PROVIDER_GOOGLE:
                $this->oauthGoogle = $uid;
                break;
            case self::PROVIDER_FACEBOOK:
                $this->oauthFacebook = $uid;
                break;
            default:
                throw new \RuntimeException('unknown provider');
        }
    }

    final protected function hasRemainingOauth($exclude)
    {
        $providers = array(self::PROVIDER_GITHUB, self::PROVIDER_GOOGLE, self::PROVIDER_FACEBOOK);
        $provider_count = 0;
        foreach ($providers as $provider) {
            if ($provider != $exclude && hasOauth($provider)) {
                $provider_count++;
            }
        }
        return $provider_count > 0;
    }

    final public function setLanguage($language)
    {
        //$this->validateLanguage($language);
        $this->language = $language;
    }

    final public function getProfileUrl()
    {
        return $this->config->get('base').'/users/'.$this->getUsername().'';
    }

    /**
     *
     * @return string
     */
    final public function getLanguage()
    {
        return $this->language;
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

class UserRepository extends EntityRepository
{

    public function getUsers()
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u')
                ->getResult();
    }

    public function getAdministrators()
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = "'.User::PERMISSION_ADMINISTRATE.'"')
                ->getResult();
    }

    public function getModerators()
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = "'.User::PERMISSION_MODERATE.'"')
                ->getResult();
    }
}
