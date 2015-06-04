<?php

namespace Lorry\Model;

use Lorry\Model;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Lorry\Repository\BannerRepository")
 */
class Banner extends Model
{
    /**
     * @Column(type="datetime", name="show_from", nullable=true)
     * @var \DateTime
     */
    protected $showFrom;

    /**
     * @Column(type="datetime", name="show_until", nullable=true)
     * @var \DateTime
     */
    protected $showUntil;

    /**
     * @ManyToOne(targetEntity="Addon")
     */
    protected $addon;

    /**
     * @OneToMany(targetEntity="BannerTranslation", mappedBy="banner", cascade={"all"})
     * @var ArrayCollection
     */
    protected $translations;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $defaultUrl;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $defaultImageUrl;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function setShowFrom($showFrom)
    {
        $this->showFrom = $showFrom;
    }

    public function getShowFrom()
    {
        return $this->showFrom;
    }

    public function setShowUntil($showUntil)
    {
        $this->showUntil = $showUntil;
    }

    public function getShowUntil()
    {
        return $this->showUntil;
    }

    /**
     *
     * @param Addon $addon
     */
    public function setAddon($addon)
    {
        $this->addon = $addon;
    }

    /**
     *
     * @return Addon
     */
    public function getAddon()
    {
        return $this->addon;
    }

    /**
     *
     * @param BannerTranslation $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     *
     * @param BannerTranslation $translation
     */
    public function addTranslation($translation)
    {
        $this->translations->add($translation);
    }

    /**
     *
     * @return BannerTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    public function getTranslation()
    {

    }

    public function setDefaultUrl($defaultUrl)
    {
        $this->defaultUrl = $defaultUrl;
    }

    public function getDefaultUrl()
    {
        return $this->defaultUrl;
    }
}
