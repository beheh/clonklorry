<?php

namespace Lorry\Model;

class LanguageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Language
     */
    protected $language;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->language = new Language;
    }

    /**
     * @covers Lorry\Model\Language::setKey
     * @covers Lorry\Model\Language::getKey
     */
    public function testKey()
    {
        $expected = 'key-TEST';
        $this->language->setKey($expected);
        $this->assertSame($expected, $this->language->getKey());

    }

    /**
     * @covers Lorry\Model\Language::__toString
     */
    public function test__toString()
    {
        $expected = 'tostring-TEST';
        $this->language->setKey($expected);
        $this->assertEquals($expected, $this->language);
        $this->assertSame($expected, (string) $this->language);
    }

}
