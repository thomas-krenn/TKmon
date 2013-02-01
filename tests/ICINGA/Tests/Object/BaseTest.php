<?php

namespace ICINGA\Tests\Object;

class BaseTest extends \PHPUnit_Framework_TestCase
{

    public function testObjectCreation1()
    {

        $hostData = new \NETWAYS\Common\ArrayObject(
            array(
                'host_name' => 'test1',
                'alias'     => 'Host Test1',
                'address'   => '127.0.0.1',

                'cf_test1'  => '11',
                'cf_test2' => '22'
            )
        );

        $host = \ICINGA\Object\Host::createObjectFromArray($hostData);

        $this->assertInstanceOf('\ICINGA\Object\Host', $host);

        $host->addCustomVariable('laola', 'ding');

        $voyager = $host->createDataVoyager(true);

        $this->assertAttributeEquals('test1', 'host_name', $voyager);
        $this->assertAttributeEquals('127.0.0.1', 'address', $voyager);
        $this->assertAttributeEquals('11', 'cf_test1', $voyager);
        $this->assertAttributeEquals('ding', 'cf_laola', $voyager);

        $this->assertNull($host->getCustomVariable('does_not_exist'));

        $host->assertObjectIsValid(); // Nothing happens
    }

    public function testObjectCreation2()
    {
        $contactData = new \NETWAYS\Common\ArrayObject(
            array(
                'alias' => 'Theodor Mueller',
                'email' => 'badabing@bingbing.com'
            )
        );

        $contact = \ICINGA\Object\Contact::createObjectFromArray($contactData);
        $contact->createObjectIdentifier();

        $this->assertEquals('theodor_mueller', $contact->contactName);
    }

    public function testStringCompilationWithError()
    {
        $hostData = new \NETWAYS\Common\ArrayObject(
            array(
                'alias'     => 'Host Test1',
                'address'   => '127.0.0.1'
            )
        );

        $host = \ICINGA\Object\Host::createObjectFromArray($hostData);

        $test = (string)$host;

        $this->assertEquals('# Could not convert to string: $hostName not set', $test);
    }
}
