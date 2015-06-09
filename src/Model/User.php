<?php

namespace Lorry\Model;

use Lorry\Model;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Lorry\Repository\UserRepository")
 * @HasLifecycleCallbacks
 * @ChangeTrackingPolicy("NOTIFY")
 */
class User extends Model
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

    /**
     * @ManyToOne(targetEntity="Language")
     * @var Language
     */
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

    /* Ownership */

    /**
     * @OneToMany(targetEntity="Addon", mappedBy="owner")
     */
    protected $ownedAddons;

    /**
     * @OneToMany(targetEntity="Comment", mappedBy="author")
     * @var Addon[]
     */
    protected $writtenComments;

    /**
     * @OneToMany(targetEntity="UserModeration", mappedBy="user")
     * @var UserModeration[]
     */
    protected $moderations;

    /**
     * @OneToMany(targetEntity="UserModeration", mappedBy="executor")
     * @var UserModeration[]
     */
    protected $executedModerations;

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
    const PROVIDER_GOOGLE = 2;
    const PROVIDER_FACEBOOK = 3;

    /* Initialize Collections */

    public function __construct()
    {
        $this->ownedAddons = new ArrayCollection();
        $this->writtenComments = new ArrayCollection();
        $this->moderations = new ArrayCollection();
        $this->executedModerations = new ArrayCollection();
    }
    /* Getters/Setters */

    public function setUsername($username)
    {
        if ($username != $this->username) {
            $this->_onPropertyChanged('username', $this->username, $username);
            $this->username = $username;
        }
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
        } else {
            $hash = null;
        }
        $this->_onPropertyChanged('passwordHash', $this->passwordHash, $hash);
        $this->incrementCounter();
        $this->passwordHash = $hash;
    }

    public function hasPassword()
    {
        return $this->passwordHash !== null;
    }

    public function matchPassword($password)
    {
        if (empty($password)) {
            return false;
        }
        return password_verify($password, $this->passwordHash) === true;
    }

    public function setEmail($email)
    {
        if ($email != $this->email) {
            $this->_onPropertyChanged('email', $this->email, $email);
            $this->email = $email;
        }
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
        $this->setRegistration(new \DateTime());
    }

    protected function setRegistration($registration)
    {
        if ($registration != $this->registration) {
            $this->_onPropertyChanged('registration', $this->registration, $registration);
            $this->registration = $registration;
        }
    }

    /**
     *
     * @return \DateTime
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    public function isActivated()
    {
        return $this->activation !== null;
    }

    public function activate()
    {
        $this->setActivation(new \DateTime());
    }

    public function deactivate()
    {
        $this->setActivation(null);
    }

    protected function setActivation($activation)
    {
        if ($activation != $this->activation) {
            $this->_onPropertyChanged('activation', $this->activation, $activation);
            $this->activation = $activation;
        }
    }

    public function regenerateSecret()
    {
        $secret = base64_encode(openssl_random_pseudo_bytes(64));
        $this->secret = $secret;
        $this->_onPropertyChanged('secret', $this->secret, $secret);
        $this->incrementCounter();
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function matchSecret($secret)
    {
        if (empty($secret) || empty($this->secret)) {
            return false;
        }
        return hash_equals($this->secret, $secret);
    }

    public function setPermissions($permissions)
    {
        if ($permissions != $this->permissions) {
            $this->_onPropertyChanged('permissions', $this->permissions, $permissions);
            $this->permissions = $permissions;
        }
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function hasPermission($permission)
    {
        return $this->getPermissions() >= $permission;
    }

    public function isAdministrator()
    {
        return $this->hasPermission(self::PERMISSION_ADMINISTRATE);
    }

    public function isModerator()
    {
        return $this->hasPermission(self::PERMISSION_MODERATE);
    }

    public function setFlags($flags)
    {
        if($this->flags != $flags) {
            $this->_onPropertyChanged('flags', $this->flags, $flags);
            $this->flags = $flags;
        }
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function setFlag($flag)
    {
        $flags = $this->getFlags();
        $flags = $flags | $flag;
        $this->setFlags($flags);
    }

    public function unsetFlag($flag)
    {
        $flags = $this->getFlags();
        $flags = $flags xor $flag;
        $this->setFlags($flags);
    }

    public function hasFlag($flag)
    {
        return !!($this->getFlags() & $flag);
    }

    public function getCounter()
    {
        $this->counter;
    }

    public function incrementCounter()
    {
        $this->setCounter($this->counter + 1);
    }

    private function setCounter($counter)
    {
        $this->_onPropertyChanged('counter', $this->counter, $counter);
        $this->counter = $counter;
    }

    public function verifyCounter($counter)
    {
        return $this->counter <= $counter;
    }

    public function setClonkforgeId($clonkforgeId)
    {
        if ($clonkforgeId != $this->clonkforgeId) {
            $this->_onPropertyChanged('clonkforgeId', $this->clonkforgeId, $clonkforgeId);
            $this->clonkforgeId = $clonkforgeId;
        }
    }

    public function getClonkforgeId()
    {
        return $this->clonkforgeId;
    }

    public function setGithubName($githubName)
    {
        if ($githubName != $this->githubName) {
            $this->_onPropertyChanged('githubName', $this->githubName, $githubName);
            $this->githubName = $githubName;
        }
    }

    public function getGithubName()
    {
        return $this->githubName;
    }

    public function hasOauth($provider)
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

    public function getOauthCount()
    {
        return count($this->getOauthArray());
    }

    public function getLoginMethodCount()
    {
        return $this->getOauthCount() + ($this->hasPassword() ? 1 : 0);
    }

    public function parseOauth($oauth)
    {
        switch ($oauth) {
            case 'github':
                return self::PROVIDER_GITHUB;
            case 'google':
                return self::PROVIDER_GOOGLE;
            case 'facebook':
                return self::PROVIDER_FACEBOOK;
            default:
                throw new \InvalidArgumentException('unknown provider');
        }
    }

    public function getOauthArray()
    {
        $result = array();
        if ($this->hasOauth(self::PROVIDER_GITHUB)) {
            $result['github'] = $this->oauthGithub;
        }
        if ($this->hasOauth(self::PROVIDER_GOOGLE)) {
            $result['google'] = $this->oauthGoogle;
        }
        if ($this->hasOauth(self::PROVIDER_FACEBOOK)) {
            $result['facebook'] = $this->oauthFacebook;
        }
        return $result;
    }

    public function setOauth($provider, $uid)
    {
        switch ($provider) {
            case self::PROVIDER_GITHUB:
                $this->_onPropertyChanged('oauthGithub', $this->oauthFacebook, $uid);
                $this->oauthGithub = $uid;
                break;
            case self::PROVIDER_GOOGLE:
                $this->_onPropertyChanged('oauthGoogle', $this->oauthFacebook, $uid);
                $this->oauthGoogle = $uid;
                break;
            case self::PROVIDER_FACEBOOK:
                $this->_onPropertyChanged('oauthFacebook', $this->oauthFacebook, $uid);
                $this->oauthFacebook = $uid;
                break;
            default:
                throw new \InvalidArgumentException('unknown provider');
        }
    }

    public function setLanguage($language)
    {
        if ($language != $this->language) {
            $this->_onPropertyChanged('language', $this->language, $language);
            $this->language = $language;
        }
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getWrittenComments()
    {
        return $this->writtenComments;
    }

    /**
     *
     * @return \Doctrine\Common\Collections\Collection|Addon[]
     */
    public function getOwnedAddons()
    {
        return $this->ownedAddons;
    }

    public function getModerations()
    {
        return $this->moderations;
    }

    public function getExecutedModerations()
    {
        return $this->executedModerations;
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
}
