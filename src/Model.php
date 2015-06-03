<?php

namespace Lorry;

abstract class Model
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    final public function getId() {
        return $this->id;
    }
}
