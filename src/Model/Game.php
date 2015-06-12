<?php

namespace Lorry\Model;

use Lorry\ApiObjectInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(readOnly=true)
 */
class Game extends AbstractModel implements ApiObjectInterface
{
    /**
     * @Column(type="string", length=16, unique=true)
     */
    protected $short;

    /**
     * @Column(type="string", unique=true)
     */
    protected $title;

    /**
     * @OneToMany(targetEntity="Addon", mappedBy="game", fetch="EXTRA_LAZY", orphanRemoval=false)
     * @var Addon[]
     */
    protected $addons;

    public function __construct()
    {
        $this->addons = new ArrayCollection();
    }

    /**
     *
     * @param string $short
     */
    public function setShort($short)
    {
        $this->short = $short;
    }

    /**
     *
     * @return string
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @return \Doctrine\Common\Collections\Collection|Addon[]
     */
    public function getAddons()
    {
        return $this->addons;
    }

    public function __toString()
    {
        return (string) $this->getTitle();
    }

    public function forApi()
    {
        return array('id' => $this->getShort(), 'title' => $this->getTitle());
    }

    public function forPresenter()
    {
        return array('short' => $this->getShort(), 'title' => $this->getTitle());
    }
}
