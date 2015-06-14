<?php

namespace Lorry\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Lorry\Repository\BannerRepository")
 */
class Banner extends AbstractModel
{
    const VISIBILITY_HIDDEN = 0;
    const VISIBILITY_PUBLIC = 1;

    // reserve VISIBILITY_USERS = 2
    /**
     * @Column(type="integer")
     */
    protected $visibility;

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
     * @ManyToOne(targetEntity="Release")
     * @JoinColumn(onDelete="CASCADE")
     */
    protected $release;

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

    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    public function getVisibility()
    {
        return $this->visibility;
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

    public function isScheduled()
    {
        $now = new \DateTime();
        return $this->visibility === self::VISIBILITY_PUBLIC && $now < $this->showFrom;
    }

    public function isActive() {
        $now = new \DateTime();
        return $this->visibility === self::VISIBILITY_PUBLIC && $now >= $this->showFrom && $now < $this->showUntil;
    }

    /**
     *
     * @param Release $release
     */
    public function setRelease($release)
    {
        $this->release = $release;
    }

    /**
     *
     * @return Release
     */
    public function getRelease()
    {
        return $this->release;
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
