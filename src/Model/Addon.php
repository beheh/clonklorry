<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ApiObjectInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Lorry\Repository\AddonRepository")
 * @HasLifecycleCallbacks
 */
class Addon extends Model implements ApiObjectInterface
{
    /**
     * @ManyToOne(targetEntity="User", inversedBy="ownedAddons")
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

    /** @Column(type="string") */
    protected $title;

    /** @Column(type="string", nullable=true) */
    protected $website;

    /** @Column(type="string", nullable=true) */
    protected $forum;

    /** @Column(type="string", nullable=true) */
    protected $bugtracker;

    /**
     * @OneToOne(targetEntity="Release")
     * @JoinColumn(name="latest_release_id")
     * @var Release
     */
    protected $latestRelease;

    /**
     * @OneToMany(targetEntity="Release", mappedBy="addon", cascade={"all"}))
     * @var Release[]
     */
    protected $releases;

    public function __construct()
    {
        $this->releases = new ArrayCollection();
    }
    /*    return array(
      'owner' => 'string',
      'short' => 'string',
      'title_en' => 'string',
      'title_de' => 'string',
      'abbreviation' => 'string',
      'game' => 'int',
      'type' => 'int',
      'introduction' => 'text',
      'description' => 'text',
      'website' => 'url',
      'bugtracker' => 'url',
      'forum' => 'url',
      'proposed_short' => 'string',
      'approval_submit' => 'datetime',
      'approval_comment' => 'text');
     */

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

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
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

    public function forApi()
    {
        return array();
    }
}
