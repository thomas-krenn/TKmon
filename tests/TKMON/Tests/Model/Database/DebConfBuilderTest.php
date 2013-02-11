<?php

namespace TKMON\Tests\Model\Database;

class DebConfBuilderTest extends \PHPUnit_Framework_TestCase
{
    private static $container;
    private static $dataPath;

    private $testFile1 = '/tmp/tkmon-dbconfig1.php';
    private $testFile2 = '/tmp/tkmon-dbconfig2.php';

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();

        self::$dataPath = dirname(dirname(dirname(__DIR__))). '/Data/Database';
    }

    protected function setUp()
    {
        $file1 = self::$dataPath. '/db-config1.php';
        $file2 = self::$dataPath. '/db-config2.php';

        copy ($file1, $this->testFile1);
        copy ($file2, $this->testFile2);

        mkdir('/tmp/tkmon'); // testFile2 config
    }

    protected function tearDown()
    {
        unlink($this->testFile1);
        unlink($this->testFile2);

        exec('rm -rf /tmp/tkmon');
    }


    public function testTestSetup()
    {
        // Dummy testing
        $this->assertFileExists($this->testFile1);
        $this->assertFileExists($this->testFile2);

        $this->assertFileExists('/tmp/tkmon');
    }

    /**
     * @expectedException TKMON\Exception\ModelException
     * @expectedExceptionMessage Database type is not supported: test7
     */
    public function testLoad1()
    {
        $builder = new \TKMON\Model\Database\DebConfBuilder();
        $builder->loadFromFile($this->testFile1);

        $this->assertEquals('test1', $builder->getUser());
        $this->assertEquals('test2', $builder->getPassword());
        $this->assertEquals('test3', $builder->getBasePath());
        $this->assertEquals('test4', $builder->getName());
        $this->assertEquals('test5', $builder->getServer());
        $this->assertEquals('test6', $builder->getPort());
        $this->assertEquals('test7', $builder->getType());

        $pdo = $builder->buildConnection();
    }

    /**
     * @expectedException \TKMON\Exception\ModelException
     * @expectedExceptionMessage File does not exist: /tmp/does/not/exists.db
     */
    public function testLoad2()
    {
        $builder = new \TKMON\Model\Database\DebConfBuilder();
        $builder->loadFromFile('/tmp/does/not/exists.db');
        $builder->buildConnection();
    }

    public function testBuild1()
    {
        $builder = new \TKMON\Model\Database\DebConfBuilder();
        $builder->loadFromFile($this->testFile2);
        $pdo = $builder->buildConnection();

        $this->assertInstanceOf('\PDO', $pdo);

        $pdo2 = $builder->buildConnection(false); // Creating a new instance

        $this->assertNotEquals(spl_object_hash($pdo), spl_object_hash($pdo2));
    }
}
