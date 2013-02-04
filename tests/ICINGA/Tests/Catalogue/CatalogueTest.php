<?php

namespace ICINGA\Tests\Catalogue;

class CatalogueTest extends \PHPUnit_Framework_TestCase
{

    private static $dataPath;

    /**
     * @var \ICINGA\Catalogue\Services
     */
    protected $catalogue;

    /**
     * @var \ICINGA\Catalogue\Provider\JsonFiles
     */
    protected $jsonProvider;

    public static function setUpBeforeClass()
    {
        self::$dataPath = dirname(dirname((__DIR__)));
    }

    protected function setUp()
    {
        $jsonData = self::$dataPath. '/Data/default-service-catalogue.json';

        $this->catalogue = new \ICINGA\Catalogue\Services();

        $this->jsonProvider = new \ICINGA\Catalogue\Provider\JsonFiles();
        $this->jsonProvider->addFile($jsonData);

        $this->catalogue->appendHandlerToChain($this->jsonProvider);

        $this->catalogue->makeReady();
    }

    protected function tearDown()
    {
        unset($this->jsonProvider);
        unset($this->serviceProvider);
    }

    public function testQuery1()
    {
        $this->assertInstanceOf('\ICINGA\Catalogue\Provider\JsonFiles', $this->jsonProvider);

        $this->assertCount(7, $this->catalogue->query('remote'));

        $this->assertCount(1, $this->catalogue->query('ping'));

        $this->assertCount(2, $this->catalogue->query('port'));

        $this->assertCount(1, $this->catalogue->query('net-ping'));

        $this->assertCount(4, $this->catalogue->query('Test'));
    }

    public function testGet()
    {
        $this->assertInstanceOf('\ICINGA\Catalogue\Provider\JsonFiles', $this->jsonProvider);

        $object = $this->catalogue->getItem('net-tcp');

        var_dump($object);
    }
}
