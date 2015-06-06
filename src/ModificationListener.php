<?php

namespace Lorry;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;

class ModificationListener implements PropertyChangedListener
{
    private $notified;

    public function __construct(NotifyPropertyChanged $monitor)
    {
        $monitor->addPropertyChangedListener($this);
        $this->notified = false;
    }

    public function isNotified()
    {
        return $this->notified;
    }

    public function propertyChanged($sender, $propertyName, $oldValue, $newValue)
    {
        $this->notified = true;
    }
}
