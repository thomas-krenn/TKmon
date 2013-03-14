<?php
namespace NETWAYS\Tests\IO;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testDescriptors()
    {

        $default_desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $proc = new \NETWAYS\IO\Process('/bin/ls');

        $this->assertEquals($default_desc, $proc->getDescriptors());

        $default_desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'r'),
            2 => array('pipe', 'w')
        );

        $proc->changeDescriptor(
            \NETWAYS\IO\Process::STDOUT,
            \NETWAYS\IO\Process::PIPE,
            \NETWAYS\IO\Process::READ
        );

        $this->assertEquals($default_desc, $proc->getDescriptors());

        $default_desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'r'),
            2 => array('file', '/tmp/test.txt', 'w')
        );

        $proc->changeDescriptor(
            \NETWAYS\IO\Process::STDERR,
            \NETWAYS\IO\Process::FILE,
            \NETWAYS\IO\Process::WRITE,
            '/tmp/test.txt'
        );

        $this->assertEquals($default_desc, $proc->getDescriptors());
    }

    public function testSTDIN()
    {
        $proc = new \NETWAYS\IO\Process('cat -');
        $proc->setInput("LALALA");
        $proc->execute();

        $this->assertEquals(0, $proc->getExitStatus());
        $this->assertEquals("LALALA", $proc->getOutput());
    }

    /**
     * @expectedException \NETWAYS\IO\Exception\ProcessException
     */
    public function testSTDERR()
    {
        $proc = new \NETWAYS\IO\Process('/bin/ls');
        $proc->addPositionalArgument('/ttt-not-exists');
        $proc->execute();
    }

    public function testSTDERR2()
    {
        $proc = new \NETWAYS\IO\Process('echo "OK" 1>&2');

        $proc->ignoreStdErr(true);

        $this->assertTrue($proc->execute());
    }

    public function testReset()
    {
        $proc = new \NETWAYS\IO\Process('echo OK1');
        $proc->addPositionalArgument('OK2');
        $proc->execute();
        $this->assertEquals('OK1 OK2'. PHP_EOL, $proc->getOutput());

        $proc->resetArguments();
        $proc->execute();

        $this->assertEquals('OK1'. PHP_EOL, $proc->getOutput());
    }

    /**
     * @expectedException \NETWAYS\IO\Exception\ProcessException
     */
    public function testExitstate()
    {
        $proc = new \NETWAYS\IO\Process('/bin/false');
        $proc->addPositionalArgument(0);
        $proc->execute();
    }

    public function testMisc()
    {
        $proc = new \NETWAYS\IO\Process('/bin/ls');
        $proc->addPositionalArgument('/tmp');
        $proc->execute();

        $this->assertTrue(is_array($proc->getStatus()));

        $status = $proc->getStatus();

        $this->assertTrue(!$status['running']);
    }

    public function testOutput()
    {
        $proc = new \NETWAYS\IO\Process('echo');
        $proc->addNamedArgument('-n');
        $proc->addPositionalArgument('OK11');
        $proc->execute();
        $this->assertEquals('OK11', $proc->getOutput());
    }

    public function testNamedArguments()
    {
        $proc = new \NETWAYS\IO\Process('cut');
        $proc->addNamedArgument('--delimiter', ';');
        $proc->addNamedArgument('--fields', '2');
        $proc->setInput('OK12;LLL2;KOK3');
        $proc->execute();
        $this->assertEquals('LLL2' . chr(10), $proc->getOutput());
    }

    public function testRuntime()
    {
        $proc = new \NETWAYS\IO\Process('sleep');
        $proc->addPositionalArgument(1);
        $proc->execute();
        $this->assertGreaterThan(0.0, $proc->getRuntime());
    }

    public function testWorkDirectory()
    {
        $proc = new \NETWAYS\IO\Process('pwd');
        $proc->setWorkDirectory('/var/log/');
        $proc->execute();
        $this->assertEquals('/var/log' . chr(10), $proc->getOutput());
    }

    public function testEnv1()
    {
        $proc = new \NETWAYS\IO\Process('/usr/bin/env');
        $proc->createLangEnvironment('de_DE.UTF-8');
        $proc->execute();

        $out = $proc->getOutput();

        $this->assertContains('LANGUAGE=de_DE.UTF-8', $out);
        $this->assertContains('LANG=de_DE.UTF-8', $out);
        $this->assertContains('LC_ALL=de_DE.UTF-8', $out);
    }

    public function testEnv2()
    {
        $proc = new \NETWAYS\IO\Process('/usr/bin/env');
        $proc->createLangEnvironment('ja_JP.EUC-JP');
        $proc->addEnvironment('TEST1', 'OK1');
        $proc->addEnvironment('TEST2', 'OK2');

        $proc->removeEnvironment('TEST1');

        $this->assertCount(4, $proc->getEnvironment());

        $proc->execute();

        $out = $proc->getOutput();

        $this->assertContains('LANG=ja_JP.EUC-JP', $out);
        $this->assertContains('TEST2=OK2', $out);
        $this->assertNotContains('TEST1=OK1', $out);

        $proc->purgeEnvironment();
        $this->assertNull($proc->getEnvironment());
    }

    public function testEnv3()
    {
        $proc = new \NETWAYS\IO\Process('/usr/bin/env');

        $testArray = array(
            'TEST1' => 'OK1',
            'TEST123' => 'OK123'
        );

        $proc->setEnvironment($testArray);

        $this->assertEquals($testArray, $proc->getEnvironment());

        $proc->execute();

        $this->assertContains('TEST123=OK123', $proc->getOutput());

    }
}
