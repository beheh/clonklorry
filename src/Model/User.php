<?php

namespace Lorry\Model;

use Lorry\Exception\ModelValueInvalidException;
use Lorry\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * @Entity(repositoryClass="Lorry\Model\UserRepository")
 * @HasLifecycleCallbacks
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

    /*     * @Column(type="datetime") */
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

    /* Ownership */

    /**
     * @OneToMany(targetEntity="Addon", mappedBy="owner")
     * @var Addon[]
     * */
    protected $ownedAddons;

    /**
     * @OneToMany(targetEntity="Comment", mappedBy="author")
     * @var Addon[]
     * */
    protected $writtenComments;

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

    /* Initialize Collections */

    public function __construct()
    {
        $this->ownedAddons = new ArrayCollection();
        $this->writtenComments = new ArrayCollection();
    }
    /* Getters/Setters */

    public function setUsername($username)
    {
        //$this->validateString($username, 3, 16);
        //$this->validateRegexp($username, '/^[a-z0-9_]+$/i');
        $this->username = $username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT,
                array('cost' => 12));
        } else {
            $hash = null;
        }
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
        return $this->activation = new \DateTime();
    }

    public function deactivate()
    {
        return $this->activation = null;
    }

    public function regenerateSecret()
    {
        $secret = base64_encode(openssl_random_pseudo_bytes(64));
        $this->secret = $secret;
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

    public function setPermission($permission)
    {
        return $this->permissions = $permission;
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
        $this->flags = $flags;
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
        return $this->counter;
    }

    public function incrementCounter()
    {
        return $this->counter++;
    }

    public function verifyCounter($counter)
    {
        return $this->counter <= $counter;
    }

    public function setClonkforgeUrl($clonkforge)
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

    public function setClonkforgeId($id)
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

    public function getClonkforgeId()
    {
        return $this->clonkforgeId;
    }

    public function getClonkforgeUrl()
    {
        $id = $this->getClonkforgeId();
        if ($id !== null) {
            return sprintf($this->config->get('clonkforge/url'), $id());
        }
        return '';
    }

    public function setGithubName($name)
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

    public function setOauth($provider, $uid)
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

    protected function hasRemainingOauth($exclude)
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

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getProfileUrl()
    {
        return $this->config->get('base').'/users/'.$this->getUsername().'';
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

    public function getAllAdministrators($firstResult = null, $maxResults = null)
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = :permission')
                ->setParameter('permission', User::PERMISSION_ADMINISTRATE)
                ->setFirstResult($firstResult)
                ->setMaxResults($maxResults)
                ->getResult();
    }

    public function getAllModerators($firstResult = null, $maxResults = null)
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = :permission')
                ->setParameter('permission', User::PERMISSION_MODERATE)
                ->setFirstResult($firstResult)
                ->setMaxResults($maxResults)
                ->getResult();
    }
}
