<?php

class RealTempFileTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $file = new \NETWAYS\IO\RealTempFileObject('test-real-temp', 'w');
        $file->fwrite('TEST');

        $fname = $file->getRealPath();

        $this->assertTrue(file_exists($fname));

        // Calling destructor
        $file = null;

        $this->assertFalse(file_exists($fname));
    }

    public function testSize()
    {
        $file = new \NETWAYS\IO\RealTempFileObject('test-real-temp', 'w');
        $test1 = $file->fwrite('1234');
        $file->fflush();
        $stat = $file->fstat();

        $this->assertEquals(4, $test1);
        $this->assertEquals(4, $stat['size']);
    }
}
