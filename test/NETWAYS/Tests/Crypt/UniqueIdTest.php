<?php
namespace NETWAYS\Tests\Crypt;

use NETWAYS\Crypt\UniqueId;

class UniqueIdTest extends \PHPUnit_Framework_TestCase {

    public function testLength1()
    {
        $idGenerator = new UniqueId(10);
        $this->assertEquals(10, mb_strlen($idGenerator->generateToken(false)));
        unset($idGenerator);

        $idGenerator = new UniqueId(64);
        $this->assertEquals(64, mb_strlen($idGenerator->generateToken(false)));
        unset($idGenerator);

        // Testing default generation
        $idGenerator = new UniqueId();
        $this->assertEquals(64, mb_strlen($idGenerator->generateToken(false)));
        unset($idGenerator);
    }

    public function testWork1()
    {
        $idGenerator = new UniqueId();
        $t = array();

        /*
         * This is not for real ;-) but this tests if we do not have an error
         * while calculating strings
         */

        for ($i = 0; $i < 100; $i++) {
            $t[] = $idGenerator->generateToken();
        }

        $x = array_unique($t);

        $this->assertEquals(json_encode($x), json_encode($t));
    }

    public function testStringObject1()
    {
        $idGenerator = new UniqueId();
        $output = (string)$idGenerator;
        $this->assertNotNull($output);
        $this->assertInternalType('string', $output);
        $this->assertGreaterThan(0, strlen($output));
    }
}
