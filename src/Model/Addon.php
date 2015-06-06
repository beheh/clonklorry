<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ApiObjectInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @Entity(repositoryClass="Lorry\Repository\AddonRepository")
 * @HasLifecycleCallbacks
 */
class Addon extends Model implements ApiObjectInterface
{
    /**
     * @ManyToOne(targetEntity="User", inversedBy="ownedAddons")
     * @JoinColumn(onDelete="SET NULL")
     * @var User
     */
    protected $owner;

    /** @Column(type="string", length=64, unique=true, nullable=true) */
    protected $short;

    /**
     * @ManyToOne(targetEntity="Game", inversedBy="addons", fetch="EAGER")
     * @var Game
     */
    protected $game;

    /** @Column(type="string", nullable=true) */
    protected $website;

    /** @Column(type="string", nullable=true) */
    protected $forum;

    /** @Column(type="string", nullable=true) */
    protected $bugtracker;

    /**
     * @OneToOne(targetEntity="Release", orphanRemoval=true)
     * @JoinColumn(name="latest_release_id", onDelete="SET NULL")
     * @var Release
     */
    protected $latestRelease;

    /**
     * @OneToMany(targetEntity="Release", mappedBy="addon", cascade={"all"}))
     * @var Release[]
     */
    protected $releases;

    /**
     * @OneToMany(targetEntity="AddonTranslation", mappedBy="addon", cascade={"all"}))
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    public function __construct()
    {
        $this->releases = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setShort($short)
    {
        $this->short = $short;
    }

    public function getShort()
    {
        return $this->short;
    }

    public function getTranslation($language)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('language', str_replace('_', '-', $language)))
            ->setMaxResults(1);
        return $this->translations->matching($criteria)->first();
    }

    public function setGame($game)
    {
        $this->game = $game;
    }

    public function getGame()
    {
        return $this->game;
    }

    /**
     *
     * @return Release[]
     */
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * @param \Lorry\Model\Release $latest
     */
    public function setLatestRelease($latest)
    {
        $this->latestRelease = $latest;
    }

    /**
     * @return \Lorry\Model\Release
     */
    public function getLatestRelease()
    {
        return $this->latestRelease;
    }

    /**
     *
     * @param AddonTranslation $translation
     */
    public function addTranslation($translation)
    {
        $this->translations->add($translation);
        $translation->setAddon($this);
    }

    /**
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    public function forApi()
    {
        return array();
    }
}
