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

    public function testGet1()
    {
        $this->assertInstanceOf('\ICINGA\Catalogue\Provider\JsonFiles', $this->jsonProvider);

        $object = $this->catalogue->getItem('net-tcp');

        $this->assertEquals('net-tcp', $object->getServiceDescription());

        $object->getCommand()->setArgumentValue(0, 80);

        $this->assertEquals('check_tcp!80', $object->getCheckCommand());

        $this->assertEquals('TCP port check', $object->getCustomVariable('label'));

        $this->assertEquals('remote, tcp, port, network', $object->getCustomVariable('tags'));
    }

    public function testGet2()
    {
        $this->assertInstanceOf('\ICINGA\Catalogue\Provider\JsonFiles', $this->jsonProvider);

        $service = $this->catalogue->getItem('net-ping');

        $host = new \ICINGA\Object\Host();
        $host->hostName = 'localhost';
        $host->alias = 'Test Host';
        $host->address = '127.0.0.1';

        $service->setHost($host);

        $this->assertEquals('check_ping!5000,100%!5000,100%', $service->getCheckCommand());

        $testString = '# Define object localhost_net-ping (service)
define service {
    service_description           net-ping
    host_name                     localhost
    check_command                 check_ping!5000,100%!5000,100%
    # Dump custom variables
    _TAGS                         remote, ping, network
    _NAME                         net-ping
    _LABEL                        Ping check
    _DESCRIPTION                  Check packet loss and round trip time
}';

     $this->assertEquals($testString, (string)$service);
    }
}
