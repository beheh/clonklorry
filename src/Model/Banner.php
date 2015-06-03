<?php

namespace Lorry\Model;

use Lorry\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * @Entity(repositoryClass="Lorry\Model\BannerRepository")
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
     * @Column(type="string")
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

class BannerRepository extends EntityRepository
{

    public function getTranslatedActiveBanners($language)
    {
        //SELECT t.title, t.subtitle, t.url, b.defaultUrl FROM Lorry\Model\Banner b LEFT JOIN b.translations t WHERE t.language = :language ORDER BY b.showFrom DESC')
        $qb = $this->_em->createQueryBuilder()
            ->select('t.title, t.subtitle, t.imageUrl, b.defaultImageUrl, t.url, b.defaultUrl')
            ->from('Lorry\Model\Banner', 'b')
            ->leftJoin('b.translations', 't')
            ->where('t.language = :language')
            ->andWhere('b.showFrom < :now OR b.showFrom IS NULL')
            ->andWhere('b.showUntil > :now OR b.showUntil IS NULL')
            ->orderBy('b.showFrom', 'DESC')
            ->setParameter('language', $language)
            ->setParameter('now', new \DateTime());
        return $qb->getQuery()->getResult();
    }
}
