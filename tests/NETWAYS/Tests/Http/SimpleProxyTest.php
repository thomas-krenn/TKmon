<?php

namespace NETWAYS\Tests\Http;

class SimpleProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testGet1()
    {
        $proxy = new \NETWAYS\Http\SimpleProxy('http://localhost');
        $data = $proxy->getContent();
        $this->assertContains('body', $data);
        $this->assertContains('</html>', $data);
    }

    public function testUrl()
    {
        $proxy = new \NETWAYS\Http\SimpleProxy('http://localhost');
        $proxy->setRequestUrl('/DINGS/test.php');
        $proxy->setParams(array(
            'test1' => 'A',
            'testX' => 'X'
        ));
        $proxy->addParam('test2', 'B');
        $proxy->removeParam('testX');

        $this->assertEquals(
            'http://localhost/DINGS/test.php?test1=A&test2=B',
            $proxy->createRequestUrl()
        );

        $proxy->purgeParams();
        $this->assertCount(0, $proxy->getParams());
    }

    public function testOptions()
    {
        $proxy = new \NETWAYS\Http\SimpleProxy('http://localhost');
        $proxy->setRequestUrl('/DINGS/test.php');
        $proxy->setDefaultUserAgent();

        $proxy->setOption(CURLOPT_TIMEOUT, 100);
        $proxy->setOption(CURLOPT_FRESH_CONNECT, true);

        $this->assertTrue($proxy->hasOption(CURLOPT_USERAGENT));
        $this->assertTrue($proxy->hasOption(CURLOPT_TIMEOUT));

        $proxy->unsetOption(CURLOPT_TIMEOUT);
        $this->assertFalse($proxy->hasOption(CURLOPT_TIMEOUT));

        $proxy->purgeOptions();
        $this->assertFalse($proxy->hasOption(CURLOPT_FRESH_CONNECT));
        $this->assertFalse($proxy->hasOption(CURLOPT_USERAGENT));
    }

    public function testHeaders()
    {
        $proxy = new \NETWAYS\Http\SimpleProxy('http://localhost');
        $proxy->addHttpHeader('X-TEST-HEADER1', 'AA');
        $proxy->addHttpHeader('x-test-header2', 'BB');
        $proxy->addHttpHeader('XXX-REMOVE-afTERWardS', 'DING');

        $proxy->removeHttpHeader('xxx-remove-afterwards');

        $content = $proxy->getContent();

        $this->assertTrue($proxy->hasOption(CURLOPT_HTTPHEADER));

        $this->assertEquals(
            array(
                'X-test-header1: AA',
                'X-test-header2: BB'
            ),
            $proxy->getOption(CURLOPT_HTTPHEADER)
        );

        $this->assertTrue($proxy->purgeHttpHeader()); // COVERAGE ;-)
    }

    public function testInfo()
    {
        $proxy = new \NETWAYS\Http\SimpleProxy('http://localhost');
        $proxy->doRequest();

        $this->assertContains('Host: localhost', $proxy->getInfo(CURLINFO_HEADER_OUT));

        $this->assertGreaterThan(0, count($proxy->getInfo()));
        $this->assertInternalType('array', $proxy->getInfo());
    }

    /**
     * @expectedException NETWAYS\Http\Exception\SimpleProxyException
     */
    public function testError()
    {
        $proxy = new \NETWAYS\Http\SimpleProxy('http://localhost/this/123213/is/never/been/found.777');
        $proxy->getContent();
    }
}
