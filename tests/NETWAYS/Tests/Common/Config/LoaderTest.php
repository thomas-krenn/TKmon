<?php

namespace NETWAYS\Tests\Common\Config;

class LoaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PDO
     */
    protected static $pdo;

    public static function setUpBeforeClass()
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('CREATE TABLE config('
        . ' id INTEGER PRIMARY KEY,'
        . ' name TEXT NOT NULL UNIQUE,'
        . ' value TEXT NOT NULL'
        . ' );'
        );

        $statement = self::$pdo->prepare('INSERT INTO config VALUES('
        . 'NULL, :key, :value);');

        $data = array(
            array('test.test1', 'oka'),
            array('test.test2', 'okb')
        );

        foreach ($data as $tuple) {
            $statement->bindValue(':key', $tuple[0]);
            $statement->bindValue(':value', $tuple[1]);
            $statement->execute();
        }
    }

    public static function tearDownAfterClass()
    {
        self::$pdo = null;
    }

    public function testLoad() {

        $config = new \NETWAYS\Common\Config();

        $pdoLoader = new \NETWAYS\Common\Config\PDOLoader(self::$pdo);
        $pdoLoader->setTable('config');
        $pdoLoader->setKeyColumn('name');
        $pdoLoader->setValueColumn('value');

        $config->load($pdoLoader);

        $this->assertEquals(2, count($config));
        $this->assertEquals('oka', $config['test.test1']);
        $this->assertEquals('okb', $config->get('test.test2'));

        $config->clear();
        $pdoLoader->rewind();
        $config->load($pdoLoader);
        $this->assertEquals(2, count($config));

    }

    public function testWrite() {

        $config = new \NETWAYS\Common\Config();

        $pdoWriter = new \NETWAYS\Common\Config\PDOPersister(self::$pdo);
        $pdoWriter->setTable('config');
        $pdoWriter->setKeyColumn('name');
        $pdoWriter->setValueColumn('value');

        $this->assertEquals('value', $pdoWriter->getValueColumn());
        $this->assertInstanceOf('\PDO', $pdoWriter->getPdo());

        $config->setPersister($pdoWriter);

        $this->assertInstanceOf('\NETWAYS\Common\Config\PDOPersister', $config->getPersister());

        $config->stopUpdates();
        $config->set('test.not1', 'oka');
        $config->set('test.not1', 'okb');
        $config->allowUpdates();

        $config->set('test.new1', 'oka');
        $config->set('test.new2', 'okb');
        $config['test.new3'] = 'okc';

        $pdoLoader = new \NETWAYS\Common\Config\PDOLoader(self::$pdo);
        $pdoLoader->setTable('config');
        $pdoLoader->setKeyColumn('name');
        $pdoLoader->setValueColumn('value');

        $config2 = new \NETWAYS\Common\Config();
        $config2->load($pdoLoader);

        $this->assertEquals(5, count($config2));

        $this->assertEquals('oka', $config2['test.new1']);
        $this->assertEquals('okc', $config2['test.new3']);

        unset($config['test.new2']);

        $config3 = new \NETWAYS\Common\Config();
        $config3->load($pdoLoader);

        $this->assertFalse($config3->offsetExists('test.new2'));

        $config->clear();

        $config4 = new \NETWAYS\Common\Config();
        $config4->load($pdoLoader);

        $this->assertEquals(0, count($config4));
    }

}
