<?php

namespace Lorry\Model;

use Lorry\Model2;

/**
 * @Entity(readOnly=true)
 */
class Game extends Model2 implements Lorry\ApiObjectInterface
{

    /** @Column(type="string", length=16, unique=true) */
    protected $short;

    public function setShort($short) {
        $this->short = $short;
    }

    public function getShort() {
        return $this->short;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    /** @OneToMany(targetEntity="Addon", mappedBy="game", fetch="EXTRA_LAZY", orphanRemoval=false) */
    protected $addons;

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
