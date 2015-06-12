<?php

namespace Lorry\Model;

/**
 * @Entity(readOnly=true)
 */
class Language extends AbstractModel
{
    /**
     * @Column(type="string", length=10, unique=true)
     */
    protected $key;

    public function setKey($key) {
        $this->key = $key;
    }

    public function getKey() {
        return $this->key;
    }

    public function __toString()
    {
        return (string) $this->getKey();
    }
}
