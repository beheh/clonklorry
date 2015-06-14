<?php

namespace Lorry\Model;

use \RuntimeException;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class Image extends FileModel {

    /** @Column(type="string", unique=true, nullable=false) */
    protected $secret = null;

    /**
     * @PrePersist
     */
    public function ensureSecret() {
        if($this->secret !== null) {
            return;
        }
        //$secret = openssl_random_pseudo_bytes(8);
        $this->secret = $secret;
        if(empty($this->secret)) {
            throw new RuntimeException('could not generate secret');
        }
    }
            
    public function getFilename()
    {
        $this->ensureSecret();
        return $this->secret;
    }
}