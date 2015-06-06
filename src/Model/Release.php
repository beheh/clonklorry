<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ApiObjectInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @Entity(repositoryClass="Lorry\Repository\ReleaseRepository")
 * @HasLifecycleCallbacks
 * @Table(name="`Release`",uniqueConstraints={@UniqueConstraint(name="addon_version", columns={"addon_id", "version"})})
 */
class Release extends Model implements ApiObjectInterface
{
    /**
     * @ManyToOne(targetEntity="Addon", inversedBy="releases", fetch="EAGER")
     * @JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Addon
     */
    protected $addon;

    /** @Column(type="string") */
    protected $version;

    /**
     * @Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $published;

    /**
     * @OneToMany(targetEntity="Comment", mappedBy="release", fetch="EXTRA_LAZY", orphanRemoval=false)
     * @var Comment[]
     */
    protected $comments;

    /*
        return array(
            'addon' => 'int',
            'version' => 'string',
            'initial' => 'date',
            'timestamp' => 'datetime',
            'ready' => 'boolean',
            'shipping' => 'boolean',
            'assetsecret' => 'string',
            'changelog' => 'text',
            'whatsnew' => 'text');
    */

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function setAddon($addon)
    {
        $this->addon = $addon;
    }

    public function getAddon()
    {
        return $this->addon;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function getVersion() {
        return $this->version;
    }

    public function publish() {
        $this->setPublished(new \DateTime());
    }

    public function setPublished($published) {
        $this->published = $published;
    }

    public function getPublished() {
        return $this->published;
    }

    final public function latest($addon)
    {
        $releases = $this->order('timestamp', true)->all()->byValue('addon',
            $addon);
        foreach ($releases as $release) {
            if (!$release->isReleased()) {
                continue;
            }
            return $release;
        }
        return null;
    }

    final public function isReleased()
    {
        if ($this->getTimestamp() === null) {
            return false;
        }

        if ($this->getTimestamp() > time()) {
            return false;
        }
        return true;
    }

    public function setInitial($initial)
    {
        return $this->setValue('initial', $initial);
    }

    public function getInitial()
    {
        return $this->getValue('initial');
    }

    public function setTimestamp($timestamp)
    {
        return $this->setValue('timestamp', $timestamp);
    }

    public function getTimestamp()
    {
        return $this->getValue('timestamp');
    }

    public function isScheduled()
    {
        return $this->getTimestamp() !== null;
    }

    public function setWhatsnew($whatsnew)
    {
        if ($whatsnew) {
            $this->validateString($whatsnew, 5, 512);
        } else {
            $whatsnew = null;
        }
        return $this->setValue('whatsnew', $whatsnew);
    }

    public function getWhatsnew()
    {
        return $this->getValue('whatsnew');
    }

    public function setChangelog($changelog)
    {
        if ($changelog) {
            $this->validateString($changelog, 5, 65536);
        } else {
            $changelog = null;
        }
        return $this->setValue('changelog', $changelog);
    }

    public function getChangelog()
    {
        return $this->getValue('changelog');
    }

    public function fetchRequirements()
    {
        return $this->persistence->build('Dependency')->all()->byRelease($this->getId());
    }

    public function fetchDependencies()
    {
        return $this->persistence->build('Dependency')->all()->byRequired($this->getId());
    }

    public function onInsert()
    {
        $this->setValue('assetsecret', md5($this->getAddon().time().uniqid()));
    }

    public function getAssetSecret()
    {
        return $this->getValue('assetsecret');
    }

    public function setShipping($shipping)
    {
        $this->setValue('shipping', $shipping);
    }

    public function isShipping()
    {
        return $this->getValue('shipping');
    }

    public function forApi()
    {
        return array(
            'addon' => $this->addon->getShort()
        );
    }
}
