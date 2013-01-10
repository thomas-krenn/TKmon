<?php
namespace NETWAYS\Tests\IO;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testDescriptors() {

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
            2 => array('file', '/tmp/test.txt' , 'w')
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
    public function testSTDERR() {
        $proc = new \NETWAYS\IO\Process('/bin/ls');
        $proc->addPositionalArgument('/ttt-not-exists');
        $proc->execute();
    }

    /**
     * @expectedException \NETWAYS\IO\Exception\ProcessException
     */
    public function testExitstate() {
        $proc = new \NETWAYS\IO\Process('/bin/false');
        $proc->addPositionalArgument(0);
        $proc->execute();
    }

    public function testMisc() {
        $proc = new \NETWAYS\IO\Process('/bin/ls');
        $proc->addPositionalArgument('/tmp');
        $proc->execute();

        $this->assertTrue(is_array($proc->getStatus()));

        $status = $proc->getStatus();

        $this->assertTrue(!$status['running']);
    }

    public function testOutput() {
        $proc = new \NETWAYS\IO\Process('echo');
        $proc->addNamedArgument('-n');
        $proc->addPositionalArgument('OK11');
        $proc->execute();
        $this->assertEquals('OK11', $proc->getOutput());
    }

    public function testNamedArguments() {
        $proc = new \NETWAYS\IO\Process('cut');
        $proc->addNamedArgument('--delimiter', ';');
        $proc->addNamedArgument('--fields', '2');
        $proc->setInput('OK12;LLL2;KOK3');
        $proc->execute();
        $this->assertEquals('LLL2'. chr(10), $proc->getOutput());
    }

    public function testRuntime() {
        $proc = new \NETWAYS\IO\Process('sleep');
        $proc->addPositionalArgument(1);
        $proc->execute();
        $this->assertGreaterThan(0.0, $proc->getRuntime());
    }

    public function testWorkDirectory() {
        $proc = new \NETWAYS\IO\Process('pwd');
        $proc->setWorkDirectory('/var/log/');
        $proc->execute();
        $this->assertEquals('/var/log'. chr(10), $proc->getOutput());
    }
}
