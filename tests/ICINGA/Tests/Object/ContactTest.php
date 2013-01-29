<?php

namespace ICINGA\Tests\Object;

class ContactTest extends \PHPUnit_Framework_TestCase
{

    function testCreate1()
    {
        $c = new \ICINGA\Object\Contact();

        $c->setContactName('mike_thunderbold');
        $c->alias = 'Mr. Mike Thunderbold';
        $c->email = 'mtb@novalidendpoint.local';

        $c->assertObjectIsValid();

        $this->assertEquals('mike_thunderbold', $c->getObjectIdentifier());

        $this->assertContains('contact_name', $c->getAttributes());
        $this->assertContains('email', $c->getAttributes());
        $this->assertContains('alias', $c->getAttributes());

        $data = (string)$c;

        $this->assertContains('define contact {'. PHP_EOL, $data);
        $this->assertContains('    contact_name                  mike_thunderbold'. PHP_EOL, $data);
        $this->assertContains('    alias                         Mr. Mike Thunderbold'. PHP_EOL, $data);
        $this->assertContains('    email                         mtb@novalidendpoint.local'. PHP_EOL, $data);
        $this->assertContains('}', $data);
    }

    /**
     * @expectedException ICINGA\Exception\ConfigException
     * @exceptedExceptionMessage $email not set
     */
    function testException1()
    {
        $c = new \ICINGA\Object\Contact();

        $c->setContactName('mike_thunderbold');
        $c->alias = 'Mr. Mike Thunderbold';

        $c->assertObjectIsValid();
    }

    /**
     * @expectedException ICINGA\Exception\ConfigException
     * @exceptedExceptionMessage $alias not set
     */
    function testException2()
    {
        $c = new \ICINGA\Object\Contact();

        $c->setContactName('mike_thunderbold');
        $c->email = 'mtb@novalidendpoint.local';

        $c->assertObjectIsValid();
    }

    /**
     * @expectedException ICINGA\Exception\ConfigException
     * @exceptedExceptionMessage $contactName not set
     */
    function testException3()
    {
        $c = new \ICINGA\Object\Contact();

        $c->email = 'mtb@novalidendpoint.local';
        $c->alias = 'Mr. Mike Thunderbold';

        $c->assertObjectIsValid();
    }

}
