<?php

namespace Lorry\Model;

use Lorry\Model;

/**
 * @Entity
 * @Table(name="BannerTranslation",uniqueConstraints={@UniqueConstraint(name="banner_language", columns={"banner_id", "language_id"})})
 */
class BannerTranslation extends Model
{
    /**
     * @ManyToOne(targetEntity="Banner", inversedBy="translations")
     * @JoinColumn(nullable=false, onDelete="CASCADE"))
     * @var Banner
     */
    protected $banner;

    /**
     * @ManyToOne(targetEntity="Language")
     * @var Language
     */
    protected $language;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $url;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $imageUrl;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $title;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $subtitle;

    /**
     * 
     * @param Banner $banner
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;
    }

    /**
     *
     * @return Banner
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     *
     * @param Language $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    public function getSubtitle()
    {
        return $this->subtitle;
    }
}
