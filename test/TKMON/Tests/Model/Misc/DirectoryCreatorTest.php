<?php

namespace TKMON\Tests\Model\Misc;

class DirectoryCreatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    private $rootDir;

    protected function setUp()
    {
        $this->rootDir = sys_get_temp_dir(). DIRECTORY_SEPARATOR. 'tkmon-path-creator-test';
        if (!is_dir($this->rootDir)) {
            mkdir($this->rootDir);
        }
    }

    protected function tearDown()
    {
        if (is_dir($this->rootDir)) {
            system('/bin/rm -rf '. $this->rootDir);
        }
    }


    public function testCreate1()
    {
        $dir1 = $this->rootDir. DIRECTORY_SEPARATOR. '/test1/test123/OK';
        $dir2 = $this->rootDir. DIRECTORY_SEPARATOR. '/testBBB/test/test/123/321/OK';

        $dir3 = '/HM/very/ugly/path';

        $dirCreator = new \TKMON\Model\Misc\DirectoryCreator();
        $dirCreator->addPath($dir1);
        $dirCreator->addPath($dir2);

        $dirCreator->addPath($dir3);
        $this->assertTrue($dirCreator->hasPath($dir3));

        $this->assertTrue($dirCreator->removePath($dir3));
        $this->assertFalse($dirCreator->removePath($dir3));

        $this->assertFalse($dirCreator->hasPath($dir3));

        $this->assertTrue($dirCreator->hasPath($dir1));
        $this->assertTrue($dirCreator->hasPath($dir2));

        $dirCreator->createPaths();

        $this->assertFileExists('/tmp/tkmon-path-creator-test/test1/test123/OK');
        $this->assertFileExists('/tmp/tkmon-path-creator-test/testBBB/test/test/123/321/OK');

        $this->assertTrue($dirCreator->createPath('/tmp/tkmon-path-creator-test/test1/test123/OK'));

        $dirCreator->purgePaths();
        $this->assertFalse($dirCreator->removePath($dir1));
        $this->assertFalse($dirCreator->removePath($dir2));
    }

    /**
     * @expectedException TKMON\Exception\ModelException
     * @expectedExceptionMessage No directories to create
     */
    public function testCreate2()
    {
        $dirCreator = new \TKMON\Model\Misc\DirectoryCreator();
        $dirCreator->createPaths();
    }
}
