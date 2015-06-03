<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ApiObjectInterface;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class Comment extends Model implements ApiObjectInterface
{
    /**
     * @ManyToOne(targetEntity="User")
     */
    protected $author;

    /**
     * @Column(type="string")
     */
    protected $body;

    /**
     * @Column(type="datetime")
     */
    protected $written;

    /**
     * @ManyToOne(targetEntity="release")
     */
    protected $release;

    /**
     *
     * @param \Lorry\Model\User $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return \Lorry\Model\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @PrePersist
     */
    public function write()
    {
        $this->setWritten(new \DateTime());
    }

    /**
     *
     * @param \DateTime $written
     */
    public function setWritten($written)
    {
        $this->written = $written;
    }

    /**
     *
     * @return \DateTime
     */
    public function getWritten()
    {
        return $this->written;
    }

    public function forApi()
    {
        return array(
            'author' => $this->author->getUsername(),
            'body' => $this->body,
            'written' => $this->written
        );
    }
}
