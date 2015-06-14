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
    protected $namespace = null;

    /**
     * @Column(type="string", unique=true)
     */
    protected $title = null;

    /**
     * @OneToMany(targetEntity="Addon", mappedBy="game", fetch="EXTRA_LAZY", orphanRemoval=false)
     * @var Addon[]
     */
    protected $addons = null;

    public function __construct()
    {
        $this->addons = new ArrayCollection();
    }

    /**
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
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
        return array('namespace' => $this->getNamespace(), 'title' => $this->getTitle());
    }
}
