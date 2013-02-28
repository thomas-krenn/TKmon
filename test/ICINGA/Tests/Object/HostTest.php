<?php

namespace ICINGA\Tests\Object;

class HostTest extends \PHPUnit_Framework_TestCase
{

    public function testSet1()
    {
        $o = new \ICINGA\Object\Host();
        $o->setRegister(true);
        $this->assertTrue($o->register);
    }

    /**
     * @expectedException ICINGA\Exception\AttributeException
     * @expectedExceptionMessage Attribute not defined: nothing
     */
    public function testSet2_1()
    {
        $o = new \ICINGA\Object\Host();
        $o->nothing=true;
    }

    /**
     * @expectedException ICINGA\Exception\AttributeException
     * @expectedExceptionMessage Attribute not defined: nothing
     */
    public function testSet2_2()
    {
        $o = new \ICINGA\Object\Host();
        $o->getNothing();
    }

    /**
     * @expectedException \ICINGA\Exception\SetException
     * @expectedExceptionMessage Setter needs exactly one value, nothing given
     */
    public function testSet3()
    {
        $o = new \ICINGA\Object\Host();
        $o->setRegistered();
    }

    public function testCreate1()
    {
        $host = new \ICINGA\Object\Host();

        $attributes = $host->getAttributes();

        $this->assertContains('host_name', $attributes);
        $this->assertContains('address', $attributes);
        $this->assertContains('hostgroups', $attributes);

        $host->setHostName('laola123');
        $host->displayName = 'DINGS1717';
        $this->assertEquals('laola123', $host->hostName);
        $this->assertEquals('DINGS1717', $host->getDisplayName());
    }

    public function testCreate2()
    {
        $host = new \ICINGA\Object\Host();
        $host->setHostName('localhost');
        $host->setAddress('127.0.0.1');
        $host->setDisplayName('Monitor Host');
        $host->eventHandlerEnabled = true;
        $host->checkCommand = 'check_host_freshness';
        $host->register = true;

        $host->addCustomVariable('TEST_A', 'testa');
        $host->addCustomVariable('test_b', 'testb');

        $definition = $host->toString();

        $this->assertContains('define host {'. PHP_EOL, $definition);
        $this->assertContains('# Dump custom variables'. PHP_EOL, $definition);

        $this->assertContains('event_handler_enabled', $definition);
        $this->assertContains('127.0.0.1', $definition);
        $this->assertContains('}', $definition);
        $this->assertContains('display_name    ', $definition);

        $this->assertContains('_TEST_A   ', $definition);
        $this->assertContains('_TEST_B   ', $definition);
        $this->assertContains('   testa'. PHP_EOL, $definition);
        $this->assertContains('   testb'. PHP_EOL, $definition);
    }

    public function testCustomVariables()
    {
        $host = new \ICINGA\Object\Host();

        $host->addCustomVariable('test_uuu', 'oku');
        $host->addCustomVariable('test_vvv', 'okv');
        $host->addCustomVariable('test_www', 'okw');

        $host->addCustomVariables(
            array(
                'test_aaa' => 'oka',
                'test_bbb' => 'okb',
                'test_ccc' => 'okc',
            )
        );

        $this->assertTrue($host->hasCustomVariables());
        $this->assertTrue($host->hasCustomVariable('TEST_VVV'));
        $this->assertTrue($host->hasCustomVariable('TEST_BBB'));



        $host->removeCustomVariable('TEST_UUU');
        $host->removeCustomVariable('TEST_CCC');

        $this->assertCount(4, $host->getCustomVariables());

        $test = array(
            'TEST_AAA' => 'oka',
            'TEST_BBB' => 'okb',
            'TEST_VVV' => 'okv',
            'TEST_WWW' => 'okw'
        );

        $this->assertEquals($test, $host->getCustomVariables());

        $host->purgeCustomVariables();
        $this->assertFalse($host->hasCustomVariables());
    }

}
