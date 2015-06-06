<?php

namespace Lorry\Model;

use Lorry\Model;

/**
 * @Entity
 * @Table(name="AddonTranslation",uniqueConstraints={@UniqueConstraint(name="addon_language", columns={"addon_id", "language_id"})})
 */
class AddonTranslation extends Model
{
    /**
     * @ManyToOne(targetEntity="Addon", inversedBy="translations")
     * @JoinColumn(nullable=false, onDelete="CASCADE"))
     * @var Addon
     */
    protected $addon;

    /**
     * @ManyToOne(targetEntity="Language")
     * @var Language
     */
    protected $language;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $title;

    public function setAddon($addon)
    {
        $this->addon = $addon;
    }

    public function getAddon()
    {
        return $this->addon;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
