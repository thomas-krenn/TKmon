<?php

namespace NETWAYS\Tests\IO;

class FileObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testChmod1()
    {
        $testFile = '/tmp/tkmon-test-chmod.txt';

        $fo = new \NETWAYS\IO\FileObject($testFile, 'w');

        $fo->fwrite('TEST123');
        $fo->fflush();

        $p1 = fileperms($testFile);
        $this->assertEquals(33204, $p1);

        $fo->chmod(0607);

        clearstatcache(); // WÜRGH!

        $p2 = fileperms($testFile);

        $this->assertEquals(33159, $p2);

        unlink($testFile);
    }
}
