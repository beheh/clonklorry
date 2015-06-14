<?php

namespace Lorry\Validator;

use Lorry\Model\User;

class UserValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UserValidator
     */
    protected $validator;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->validator = new UserValidator;
    }

    protected function createWorkingUser()
    {
        $user = new User();
        $user->setUsername('Wipf');
        $user->setEmail('wipf@example.com');
        $user->setPassword('Siedlerclonk');
        return $user;
    }

    public function testValidation()
    {
        $user = $this->createWorkingUser();
        $this->validator->validate($user);
    }

    /**
     * @expectedException \Lorry\Exception\ValidationException
     */
    public function testUsernameTooShort()
    {
        $user = $this->createWorkingUser();
        $user->setUsername('w');
        $this->validator->validate($user);
    }

    /**
     * @expectedException \Lorry\Exception\ValidationException
     */
    public function testUsernameTooLong()
    {
        $user = $this->createWorkingUser();
        $user->setUsername('WipfWipfWipfWipfWipfWipfWipfWipfWipf');
        $this->validator->validate($user);
    }

    /**
     * @expectedException \Lorry\Exception\ValidationException
     */
    public function testEmailAddress()
    {
        $user = $this->createWorkingUser();
        $user->setEmail('invalid@@');
        $this->validator->validate($user);
    }

}
