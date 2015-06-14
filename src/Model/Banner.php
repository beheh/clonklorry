<?php

namespace Lorry\Model;

use Doctrine\Common\Collections\ArrayCollection;
use \DateTime;

/**
 * @Entity(repositoryClass="Lorry\Repository\BannerRepository")
 */
class Banner extends AbstractModel
{
    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $name = null;

    const VISIBILITY_HIDDEN = 0;
    const VISIBILITY_PUBLIC = 1;

    // reserve VISIBILITY_USERS = 2
    /**
     * @Column(type="integer")
     */
    protected $visibility = self::VISIBILITY_HIDDEN;

    /**
     * @Column(type="datetime", name="show_from", nullable=true)
     * @var \DateTime
     */
    protected $showFrom = null;

    /**
     * @Column(type="datetime", name="show_until", nullable=true)
     * @var \DateTime
     */
    protected $showUntil = null;

    /**
     * @ManyToOne(targetEntity="Release")
     * @JoinColumn(onDelete="CASCADE", nullable=true)
     */
    protected $release = null;

    /**
     * @OneToMany(targetEntity="BannerTranslation", mappedBy="banner", cascade={"all"})
     * @var ArrayCollection
     */
    protected $translations = null;

    /**
     * @Column(name="default_url", type="string", nullable=true)
     * @var string
     */
    protected $defaultUrl = null;

    /**
     * @ManyToOne(targetEntity="Image")
     * @JoinColumn(name="default_image_id", nullable=true, onDelete="SET NULL")
     * @var Image
     */
    protected $defaultImage = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
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
        $now = new DateTime();
        return $this->visibility === self::VISIBILITY_PUBLIC && $now < $this->showFrom;
    }

    public function isActive() {
        $now = new DateTime();
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

    public function getTranslation($language_key)
    {
        foreach($this->translations as $translation) {
            if($translation->getLanguage() !== null && $translation->getLanguage()->getKey() == $language_key) {
                return $translation;
            }
        }
        return null;
    }

    public function setDefaultUrl($defaultUrl)
    {
        $this->defaultUrl = $defaultUrl;
    }

    public function getDefaultUrl()
    {
        return $this->defaultUrl;
    }

    public function setDefaultImage($image)
    {
        $this->defaultImage = $image;
    }

    public function getDefaultImage() {
        return $this->defaultImage;
    }
}
