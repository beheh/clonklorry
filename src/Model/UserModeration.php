<?php

namespace Lorry\Model;

use Lorry\Model;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class UserModeration extends Model
{
    /**
     * @ManyToOne(targetEntity="User", inversedBy="moderations", cascade={"all"}, fetch="EAGER")
     * @JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="executedModerations", fetch="EAGER")
     * @JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $executor;

    /**
     * @Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @Column(type="string")
     */
    protected $action;

    /**
     * @Column(type="string")
     */
    protected $originalValue;

    /**
     * @Column(type="string")
     */
    protected $finalValue;

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setExecutor($executor)
    {
        $this->executor = $executor;
    }

    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * @PrePersist
     */
    public function execute() {
        $this->setTimestamp(new \DateTime());
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function getAction() {
        return $this->action;
    }

    public function setOriginalValue($originalValue)
    {
        $this->originalValue = $originalValue;
    }

    public function getOriginalValue()
    {
        return $this->originalValue;
    }

    public function setFinalValue($finalValue)
    {
        $this->finalValue = $finalValue;
    }

    public function getFinalValue()
    {
        return $this->finalValue;
    }
}
