<?php

namespace NETWAYS\Tests\Http;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate() {
        $s = new \NETWAYS\Http\Session();
        $s->setDomain('localhost');
        $s->setIsSecured(false);
        $s->setPath('/');
        $s->setLifetime(300);
        $s->setName('test-phpunit');
        $s->start();

        $this->assertTrue(strlen($s->getSessionId()) > 0);

        $s->destroySession();
    }

    public function testCreate2() {
        $s = new \NETWAYS\Http\Session();
        $s->setDomain('www.foo.bar');
        $s->setIsSecured(false);
        $s->setPath('/');
        $s->setLifetime(300);
        $s->setName('test-phpunit');
        $s->start();

        $this->assertTrue(strlen($s->getSessionId()) > 0);

        $s['test'] = 'ok1';

        $this->assertTrue(count($s) === 1);

        $this->assertEquals('ok1', $s->offsetGet('test'));

        $this->assertTrue($s->offsetExists('test'));

        unset($s['test']);

        $this->assertFalse($s->offsetExists('test'));

        $sessId = $s->getSessionId();
        $s->regenerateSessionId();
        $this->assertFalse($sessId === $s->getSessionId());

        $s->destroySession();
    }

    /**
     * @expectedException NETWAYS\Common\Exception
     */
    public function testCreate3() {
        $s = new \NETWAYS\Http\Session();
        $s->start();
    }
}
