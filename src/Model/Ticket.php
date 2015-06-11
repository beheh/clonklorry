<?php

namespace Lorry\Model;

use Lorry\Model;

/**
 * @Entity(repositoryClass="Lorry\Repository\TicketRepository")
 * @HasLifecycleCallbacks
 */
class Ticket extends Model
{
    /**
     * @ManyToOne(targetEntity="User", fetch="EAGER")
     * @JoinColumn(name="associated_user_id", nullable=true)
     */
    protected $associatedUser;

    /**
     * @Column(type="string", name="response_email_address")
     */
    protected $responseEmailAddress;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    protected $submitted;

    /**
     * @Column(type="string")
     */
    protected $subject;

    /**
     * @Column(type="text")
     */
    protected $message;

    /**
     * @Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $assigned;

    /**
     * @ManyToOne(targetEntity="User", fetch="EAGER")
     * @JoinColumn(name="assigned_to_id")
     */
    protected $assignedTo;

    public function setResponseEmail($responseEmailAddress)
    {
        $this->responseEmailAddress = $responseEmailAddress;
    }

    public function getResponseEmailAddress()
    {
        return $this->responseEmailAddress;
    }

    /**
     * @PrePersist
     */
    public function submit()
    {
        $this->setSubmitted(new \DateTime());
    }

    protected function setSubmitted($submitted) {
        $this->submitted = $submitted;
    }

    public function getSubmitted() {
        return $this->submitted;
    }

    public function setAssociatedUser($user)
    {
        $this->associatedUser = $user;
    }

    public function getAssociatedUser()
    {
        return $this->associatedUser;
    }

    public function assign($user)
    {
        $this->setAssigned(new \DateTime());
        $this->setAssignedTo($user);
    }

    protected function setAssignedTo($user)
    {
        $this->assignedTo = $user;
    }

    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    protected function setAssigned($assigned)
    {
        $this->assigned = $assigned;
    }

    public function getAssigned()
    {
        return $this->assigned;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
