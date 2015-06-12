<?php

namespace Lorry\Model;

use \DateTime;
use Lorry\ModificationListener;

class UserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var User
     */
    protected $user;
    protected $monitor;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->user = new User;
        $this->monitor = new ModificationListener($this->user);
    }

    /**
     * @covers Lorry\Model\User::setUsername
     * @covers Lorry\Model\User::getUsername
     */
    public function testUsername()
    {
        $this->assertNull($this->user->getUsername());
        $expected = 'Wipf';
        $this->user->setUsername($expected);
        $this->assertSame($expected, $this->user->getUsername());
    }

    /**
     * @covers Lorry\Model\User::setPassword
     * @covers Lorry\Model\User::matchPassword
     */
    public function testPassword()
    {
        $expected = 'password123';
        $this->user->setPassword($expected);
        $this->assertTrue($this->user->matchPassword($expected));
        $this->assertFalse($this->user->matchPassword('somethingelse'));
    }

    /**
     * @covers Lorry\Model\User::setPassword
     * @covers Lorry\Model\User::matchPassword
     */
    public function testNullPassword()
    {
        $this->user->setPassword(null);
        $this->assertFalse($this->user->matchPassword(null));
        $this->assertFalse($this->user->matchPassword('anything'));
    }

    /**
     * @covers Lorry\Model\User::setPassword
     * @covers Lorry\Model\User::hasPassword
     */
    public function testHasPassword()
    {
        $this->user->setPassword('password123');
        $this->assertTrue($this->user->hasPassword());
        $this->user->setPassword(null);
        $this->assertFalse($this->user->hasPassword());
    }

    /**
     * @covers Lorry\Model\User::setEmail
     * @covers Lorry\Model\User::getEmail
     */
    public function testEmail()
    {
        $this->assertNull($this->user->getEmail());
        $expected = 'wipf@example.com';
        $this->user->setEmail($expected);
        $this->assertSame($expected, $this->user->getEmail());
    }

    /**
     * @covers Lorry\Model\User::register
     * @covers Lorry\Model\User::getRegistration
     */
    public function testRegistration()
    {
        $this->assertNull($this->user->getRegistration());
        $this->user->register();
        $this->assertEquals(new DateTime, $this->user->getRegistration());
    }

    /**
     * @covers Lorry\Model\User::activate
     * @covers Lorry\Model\User::deactivate
     * @covers Lorry\Model\User::getActivation
     * @covers Lorry\Model\User::isActivated
     */
    public function testActivation()
    {
        $this->user->deactivate();
        $this->user->activate();
        $this->assertEquals(new DateTime, $this->user->getActivation());
        $this->assertTrue($this->user->isActivated());
        $this->user->deactivate();
        $this->assertNull($this->user->getActivation());
        $this->assertFalse($this->user->isActivated());
    }

    public function testDefaultActivation() {
        $defaultUser = new User();
        $this->assertNull($defaultUser->getActivation());
        $this->assertFalse($this->user->isActivated());
    }

    /**
     * @covers Lorry\Model\User::regenerateSecret
     * @todo   Implement testRegenerateSecret().
     */
    public function testRegenerateSecret()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getSecret
     * @covers Lorry\Model\User::matchSecret
     * @covers Lorry\Model\User::regenerateSecret
     */
    public function testSecret()
    {
        $this->assertNull($this->user->getSecret());
        $this->assertFalse($this->user->matchSecret(null));
        $this->user->regenerateSecret();
        $expected = $this->user->getSecret();
        $this->assertNotNull($expected);
        $this->assertTrue($this->user->matchSecret($expected));
    }

    /**
     * @covers Lorry\Model\User::setPermissions
     * @covers Lorry\Model\User::getPermissions
     */
    public function testPermissions()
    {
        $expected = User::PERMISSION_READ;
        $this->user->setPermissions(User::PERMISSION_READ);
        $this->assertSame($expected, $this->user->getPermissions());
    }

    public function testDefaultPermissions()
    {
        $defaultUser = new User();
        $this->assertSame(User::PERMISSION_READ, $defaultUser->getPermissions());
        $this->assertFalse($defaultUser->isModerator());
        $this->assertFalse($defaultUser->isAdministrator());
    }

    /**
     * @covers Lorry\Model\User::isAdministrator
     */
    public function testIsAdministrator()
    {
        $this->user->setPermissions(User::PERMISSION_READ);
        $this->assertFalse($this->user->isAdministrator());
        $this->user->setPermissions(User::PERMISSION_MODERATE);
        $this->assertFalse($this->user->isAdministrator());
        $this->user->setPermissions(User::PERMISSION_ADMINISTRATE);
        $this->assertTrue($this->user->isAdministrator());
    }

    /**
     * @covers Lorry\Model\User::isModerator
     */
    public function testIsModerator()
    {
        $this->user->setPermissions(User::PERMISSION_READ);
        $this->assertFalse($this->user->isModerator());
        $this->user->setPermissions(User::PERMISSION_MODERATE);
        $this->assertTrue($this->user->isModerator());
        $this->user->setPermissions(User::PERMISSION_ADMINISTRATE);
        $this->assertTrue($this->user->isModerator());
    }

    /**
     * @covers Lorry\Model\User::setFlags
     * @todo   Implement testSetFlags().
     */
    public function testSetFlags()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getFlags
     * @todo   Implement testGetFlags().
     */
    public function testGetFlags()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::setFlag
     * @todo   Implement testSetFlag().
     */
    public function testSetFlag()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::unsetFlag
     * @todo   Implement testUnsetFlag().
     */
    public function testUnsetFlag()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::hasFlag
     * @todo   Implement testHasFlag().
     */
    public function testHasFlag()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getCounter
     * @todo   Implement testGetCounter().
     */
    public function testGetCounter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::incrementCounter
     * @todo   Implement testIncrementCounter().
     */
    public function testIncrementCounter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::verifyCounter
     * @todo   Implement testVerifyCounter().
     */
    public function testVerifyCounter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::setClonkforgeId
     * @todo   Implement testSetClonkforgeId().
     */
    public function testSetClonkforgeId()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getClonkforgeId
     * @todo   Implement testGetClonkforgeId().
     */
    public function testGetClonkforgeId()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::setGithubName
     * @todo   Implement testSetGithubName().
     */
    public function testSetGithubName()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getGithubName
     * @todo   Implement testGetGithubName().
     */
    public function testGetGithubName()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::hasOauth
     * @todo   Implement testHasOauth().
     */
    public function testHasOauth()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getOauthCount
     * @todo   Implement testGetOauthCount().
     */
    public function testGetOauthCount()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getLoginMethodCount
     * @todo   Implement testGetLoginMethodCount().
     */
    public function testGetLoginMethodCount()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::parseOauth
     * @todo   Implement testParseOauth().
     */
    public function testParseOauth()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getOauthArray
     * @todo   Implement testGetOauthArray().
     */
    public function testGetOauthArray()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::setOauth
     * @todo   Implement testSetOauth().
     */
    public function testSetOauth()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::setLanguage
     * @todo   Implement testSetLanguage().
     */
    public function testSetLanguage()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getLanguage
     * @todo   Implement testGetLanguage().
     */
    public function testGetLanguage()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getWrittenComments
     * @todo   Implement testGetWrittenComments().
     */
    public function testGetWrittenComments()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getOwnedAddons
     * @todo   Implement testGetOwnedAddons().
     */
    public function testGetOwnedAddons()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getModerations
     * @todo   Implement testGetModerations().
     */
    public function testGetModerations()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::getExecutedModerations
     * @todo   Implement testGetExecutedModerations().
     */
    public function testGetExecutedModerations()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Lorry\Model\User::__toString
     */
    public function test__toString()
    {
        $expected = 'Wipf';
        $this->user->setUsername($expected);
        $this->assertSame((string) $this->user, $expected);
    }

    /**
     * @covers Lorry\Model\User::forApi
     * @todo   Implement testForApi().
     */
    public function testForApi()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
