<?php

namespace NETWAYS\Tests\Http;

class CgiParamsTest extends \PHPUnit_Framework_TestCase
{

    public function testRequestData()
    {
        $_GET = array('test1' => 'ok1', 'test2' => 'ok2');
        $_POST = array('test3' => 'ok3', 'test4' => 'ok4');

        $cgi = new \NETWAYS\Http\CgiParams();

        $this->assertEquals('ok1', $cgi->getParameter('test1'));
        $this->assertEquals('ok4', $cgi->getParameter('test4'));

        $this->assertEquals('okCC', $cgi->getParameter('testXX', 'okCC'));
    }

    public function testCookieData()
    {
        $_COOKIE['tkmon-loggin-session'] = 'asdasdasdasdasd123123';
        $cgi = new \NETWAYS\Http\CgiParams();

        $this->assertEquals('asdasdasdasdasd123123', $cgi->getParameter('tkmon-loggin-session', null, 'cookie'));
    }

    public function testHeaderData()
    {
        $cgi = new \NETWAYS\Http\CgiParams();

        $this->assertTrue(strlen($cgi->getParameter('PATH', null, 'header')) > 0);
    }

    public function testNull()
    {
        $cgi = new \NETWAYS\Http\CgiParams();
        $this->assertInternalType('array', $cgi->getAll('header'));
        $this->assertEquals(null, $cgi->getAll('xxx'));
    }

    public function testHasParameter()
    {
        $cgi = new \NETWAYS\Http\CgiParams();
        $this->assertTrue($cgi->hasParameter('PATH', 'header'));
        $this->assertFalse($cgi->hasParameter('PATH'));
    }

    public function testSetGet()
    {
        $cgi = new \NETWAYS\Http\CgiParams();
        $cgi->setParameter('test1', 'oka', 'cookie');
        $this->assertEquals('oka', $cgi->getParameter('test1', null, 'cookie'));
    }

}
