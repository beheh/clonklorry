<?php

namespace Lorry\Model;

use Lorry\Model2;

class Comment extends Model
{

    /** @ManyToOne(targetEntity="User") */
    protected $author;

    /** @Column(type="string") */
    protected $body;

    /** @Column(type="datetime") */
    protected $written;
}
