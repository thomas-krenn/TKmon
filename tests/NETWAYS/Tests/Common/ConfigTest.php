<?php

namespace NETWAYS\Tests\Common;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testLoadDirectory()
    {

        $dir = dirname(dirname(dirname(__FILE__)));
        $dataDir = $dir . DIRECTORY_SEPARATOR . 'Data/Config1';

        $c = new \NETWAYS\Common\Config();
        $c->loadDirectory($dataDir);

        $this->assertEquals('oka', $c->get('test1'));
        $this->assertEquals('okb', $c->get('test2'));
        $this->assertEquals('oka/okc', $c->get('test3'));

    }

    public function testLoadFile()
    {

        $dir = dirname(dirname(dirname(__FILE__)));
        $dataFile = $dir . DIRECTORY_SEPARATOR . 'Data/Config2/1.json';

        $c = new \NETWAYS\Common\Config();
        $c->loadFile($dataFile);

        $this->assertEquals('oka', $c['test1']);
        $this->assertEquals('oka/okb', $c['test2']);

    }

    public function testGetDefault()
    {
        $dir = dirname(dirname(dirname(__FILE__)));
        $dataDir = $dir . DIRECTORY_SEPARATOR . 'Data/Config1';

        $c = new \NETWAYS\Common\Config();
        $c->loadDirectory($dataDir);

        $this->assertEquals('oka', $c->get('test1', 'okx'));
        $this->assertEquals('okxx', $c->get('testXX', 'okxx'));

        $c->set('test.null', null);
        $this->assertEquals('okxx', $c->get('test.null', 'okxx'));
    }

    public function testMaxIterations()
    {
        $c = new \NETWAYS\Common\Config();
        $letters = range('a', 'z');
        foreach ($letters as $letter) {
            $c->set($letter, $letter);
        }

        $testFmt = '{' . implode('}{', $letters) . '}';
        $c->set('test.abc', $testFmt);

        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', $c['test.abc']);
    }

}
