<?php

namespace NETWAYS\Tests\Intl;

use NETWAYS\Intl\SimpleTranslator;

class SimpleTranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function testTranslator1()
    {
        $struct = new \stdClass();
        $struct->en_US = 'test-data1';
        $struct->de_DE = 'test-data2';

        $translator = new SimpleTranslator();
        $this->assertEquals('test-data1', $translator->translate($struct));

        $translator->setLocale('de_DE');
        $this->assertEquals('test-data2', $translator->translate($struct));

        $translator->setLocale('en_US');
        $this->assertEquals('test-data1', $translator->translate($struct));
    }

    public function testTranslator2()
    {
        $struct = new \stdClass();
        $struct->de_DE = 'test-data2';

        $translator = new SimpleTranslator('en_US', 'de_DE');
        $this->assertEquals('test-data2', $translator->translate($struct));
    }

    /**
     * @expectedException NETWAYS\Intl\Exception\SimpleTranslatorException
     * @expectedExceptionMessage Locale not found: de_DE, en_US
     */
    public function testTranslator3()
    {
        $struct = new \stdClass();

        $translator = new SimpleTranslator('de_DE');
        $translator->translate($struct);
    }

    /**
     * @expectedException NETWAYS\Intl\Exception\SimpleTranslatorException
     * @expectedExceptionMessage Default locale not defined
     */
    public function testTranslator4()
    {
        $struct = new \stdClass();

        $translator = new SimpleTranslator('de_DE');
        $translator->setDefaultLocale('');
        $translator->translate($struct);
    }
}