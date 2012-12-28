<?php
namespace NETWAYS\Tests\Http;

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
}
