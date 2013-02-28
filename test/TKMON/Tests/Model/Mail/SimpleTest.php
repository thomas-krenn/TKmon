<?php

namespace TKMON\Tests\Model\Mail;

class SimpleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Pimple
     */
    private static $container;

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();
    }

    public function testCreate()
    {
        $mail = new \TKMON\Model\Mail\Simple(self::$container);

        $mail->addHeader('x-test-test', 'OK1');
        $mail->setSender('foo@localhost.local');
        $header = $mail->getHeaderAsString();

        $this->assertContains('From: foo@localhost.local', $header);
        $this->assertContains('Reply-To: foo@localhost.local', $header);
        $this->assertContains('X-Mailer: tkmon-test-0.0.0', $header);
        $this->assertContains('X-Test-Test: OK1'. chr(13). chr(10), $header);
        $this->assertCount(4, $mail->getHeaders());
    }

    public function testSend()
    {
        $this->markTestSkipped('Could not validate target');

        $mail = new \TKMON\Model\Mail\Simple(self::$container);

        $mail->addHeader('x-test-test', 'OK1');
        $mail->setSender('info@mydomain.de');
        $mail->setTo('target@mydomain.de');
        $mail->setContent('OK1');
        $mail->setSubject('OK2');
        $header = $mail->getHeaderAsString();
        $mail->sendMail();
    }

    public function testAttributes()
    {
        $mail = new \TKMON\Model\Mail\Simple(self::$container);

        $mail->setSender('laola@laola.org');
        $mail->setTo('lapaloma@lapaloma.org');
        $mail->setContent('OK123');
        $mail->setSubject('OK321');
        $mail->addHeader('X-TEST-REMOVE', 'true');


        $this->assertContains('X-Test-Remove: true', $mail->getHeaderAsString());
        $mail->removeHeader('x-test-remove');
        $this->assertNotContains('X-Test-Remove: true', $mail->getHeaderAsString());

        $mail->purgeHeaders();
        $this->assertNotContains('From: laola', $mail->getHeaderAsString());

        $this->assertEquals('OK123', $mail->getContent());
        $this->assertEquals('OK321', $mail->getSubject());
        $this->assertEquals('laola@laola.org', $mail->getSender());
        $this->assertEquals('lapaloma@lapaloma.org', $mail->getTo());

        $this->assertContains('-f \'laola', $mail->getOptionsAsString());

        $mail->resetState();

        $this->assertEmpty($mail->getHeaderAsString());
        $this->assertEmpty($mail->getTo());

    }
}
