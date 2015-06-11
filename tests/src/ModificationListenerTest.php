<?php

namespace Lorry;

class ModificationListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ModificationListener
     */
    protected $listener;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $stub = $this->getMockBuilder('Doctrine\Common\NotifyPropertyChanged')
                ->getMock();
        $this->listener = new ModificationListener($stub);
    }

    /**
     * @covers Lorry\ModificationListener::isNotified
     * @covers Lorry\ModificationListener::propertyChanged
     */
    public function testNotifications()
    {
        $this->assertFalse($this->listener->isNotified());
        $this->listener->propertyChanged($this, 'key', 'oldValue', 'newValue');
        $this->assertTrue($this->listener->isNotified());
    }

}
