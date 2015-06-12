<?php

namespace Lorry\Model;

class Dependency extends AbstractModel
{

    public function getTable()
    {
        return 'dependency';
    }

    public function getSchema()
    {
        return array(
            'release' => 'int',
            'requires' => 'int');
    }

    public function setRelease($release)
    {
        return $this->setValue('release', $release);
    }

    public function byRelease($release)
    {
        return $this->byValue('release', $release);
    }

    public function getRelease()
    {
        return $this->getValue('release');
    }

    /**
     * @return Release
     */
    public function fetchRelease()
    {
        return $this->fetch('Release', 'release');
    }

    public function setRequires($requires)
    {
        return $this->setValue('requires', $requires);
    }

    public function byRequires($requires)
    {
        return $this->byValue('requires', $requires);
    }

    public function getRequires()
    {
        return $this->getValue('requires');
    }

    /**
     * @return Release
     */
    public function fetchRequires()
    {
        return $this->fetch('Release', 'requires');
    }
}
