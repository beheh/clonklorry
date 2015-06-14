<?php

namespace Lorry\Model;

abstract class FileModel extends AbstractModel {
    /**
    * @Column(type="guid", unique=true)
    * @GeneratedValue(strategy="UUID")
    */
    protected $guid = null;

    final public function getGuid() {
        return $this->guid;
    }
}