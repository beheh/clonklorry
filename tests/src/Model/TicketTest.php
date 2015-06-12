<?php

namespace Lorry\Model;

use \DateTime;

class TicketTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->ticket = new Ticket;
    }

    /**
     * @covers Lorry\Model\Ticket::setResponseEmail
     * @covers Lorry\Model\Ticket::getResponseEmailAddress
     */
    public function testResponseEmail()
    {
        $this->assertNull($this->ticket->getResponseEmailAddress());
        $expected = 'response@example.com';
        $this->ticket->setResponseEmail($expected);
        $this->assertSame($expected, $this->ticket->getResponseEmailAddress());
    }

    /**
     * @covers Lorry\Model\Ticket::submit
     * @covers Lorry\Model\Ticket::getSubmitted
     */
    public function testSubmited()
    {
        $this->assertNull($this->ticket->getSubmitted());
        $this->ticket->submit();
        $this->assertEquals(new DateTime, $this->ticket->getSubmitted());
    }

    /**
     * @covers Lorry\Model\Ticket::setAssociatedUser
     * @covers Lorry\Model\Ticket::getAssociatedUser
     */
    public function testSetAssociatedUser()
    {
        $this->assertNull($this->ticket->getAssociatedUser());
        $expected = new User();
        $this->ticket->setAssociatedUser($expected);
        $this->assertSame($expected, $this->ticket->getAssociatedUser());
    }

    /**
     * @covers Lorry\Model\Ticket::assign
     * @covers Lorry\Model\Ticket::getAssigned
     * @covers Lorry\Model\Ticket::getAssignedTo
     */
    public function testAssigned()
    {
        $this->assertNull($this->ticket->getAssigned());
        $this->assertNull($this->ticket->getAssignedTo());
        $expected = new User();
        $this->ticket->assign($expected);
        $this->assertEquals(new \DateTime(), $this->ticket->getAssigned());
        $this->assertSame($expected, $this->ticket->getAssignedTo());
    }

    /**
     * @covers Lorry\Model\Ticket::setSubject
     * @covers Lorry\Model\Ticket::getSubject
     */
    public function testSubject()
    {
        $this->assertNull($this->ticket->getSubject());
        $expected = 'Hi there!';
        $this->ticket->setSubject($expected);
        $this->assertSame($expected, $this->ticket->getSubject());
    }

    /**
     * @covers Lorry\Model\Ticket::setMessage
     * @covers Lorry\Model\Ticket::getMessage
     */
    public function testMessage()
    {
        $this->assertNull($this->ticket->getMessage());
        $expected = 'Hi there, I have some problem. Can you help me?';
        $this->ticket->setMessage($expected);
        $this->assertSame($expected, $this->ticket->getMessage());
    }

}
