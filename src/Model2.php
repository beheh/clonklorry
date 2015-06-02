<?php

namespace Lorry;

abstract class Model2
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
