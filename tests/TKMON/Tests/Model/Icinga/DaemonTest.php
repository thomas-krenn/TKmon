<?php

namespace TKMON\Tests\Model\Icinga;

class DaemonTest extends \PHPUnit_Framework_TestCase
{

    private static $container;
    private static $dataPath;
    private static $configFile = '/tmp/tkmon-postfix-main.cf';

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();

        self::$dataPath = dirname(dirname(dirname(__DIR__)));
    }

    public function testStatusFile1()
    {

        $statusFile = self::$dataPath . '/Data/Icinga/status.dat';
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->setStatusFile($statusFile);
        $data->load();

        $this->assertEquals('1.8.4', $data->getInfoVersion());
        $this->assertEquals('1358868710', $data->getInfoCreated());

        // Get all
        $testRecord = $data->getProgramStatus();
        $this->assertInternalType('array', $testRecord);
        $this->assertCount(43, $testRecord);

        $this->assertArrayHasKey('modified_host_attributes', $testRecord);
        $this->assertArrayHasKey('modified_service_attributes', $testRecord);
        $this->assertArrayHasKey('icinga_pid', $testRecord);
        $this->assertArrayHasKey('daemon_mode', $testRecord);
        $this->assertArrayHasKey('program_start', $testRecord);
        $this->assertArrayHasKey('last_command_check', $testRecord);
        $this->assertArrayHasKey('last_log_rotation', $testRecord);
        $this->assertArrayHasKey('enable_notifications', $testRecord);
        $this->assertArrayHasKey('disable_notifications_expire_time', $testRecord);
        $this->assertArrayHasKey('active_service_checks_enabled', $testRecord);
        $this->assertArrayHasKey('passive_service_checks_enabled', $testRecord);
        $this->assertArrayHasKey('active_host_checks_enabled', $testRecord);
        $this->assertArrayHasKey('passive_host_checks_enabled', $testRecord);
        $this->assertArrayHasKey('enable_event_handlers', $testRecord);
        $this->assertArrayHasKey('obsess_over_services', $testRecord);
        $this->assertArrayHasKey('obsess_over_hosts', $testRecord);
        $this->assertArrayHasKey('check_service_freshness', $testRecord);
        $this->assertArrayHasKey('check_host_freshness', $testRecord);
        $this->assertArrayHasKey('enable_flap_detection', $testRecord);
        $this->assertArrayHasKey('enable_failure_prediction', $testRecord);
        $this->assertArrayHasKey('process_performance_data', $testRecord);
        $this->assertArrayHasKey('global_host_event_handler', $testRecord);
        $this->assertArrayHasKey('global_service_event_handler', $testRecord);
        $this->assertArrayHasKey('next_comment_id', $testRecord);
        $this->assertArrayHasKey('next_downtime_id', $testRecord);
        $this->assertArrayHasKey('next_event_id', $testRecord);
        $this->assertArrayHasKey('next_problem_id', $testRecord);
        $this->assertArrayHasKey('next_notification_id', $testRecord);
        $this->assertArrayHasKey('total_external_command_buffer_slots', $testRecord);
        $this->assertArrayHasKey('used_external_command_buffer_slots', $testRecord);
        $this->assertArrayHasKey('high_external_command_buffer_slots', $testRecord);
        $this->assertArrayHasKey('active_scheduled_host_check_stats', $testRecord);
        $this->assertArrayHasKey('active_ondemand_host_check_stats', $testRecord);
        $this->assertArrayHasKey('passive_host_check_stats', $testRecord);
        $this->assertArrayHasKey('active_scheduled_service_check_stats', $testRecord);
        $this->assertArrayHasKey('active_ondemand_service_check_stats', $testRecord);
        $this->assertArrayHasKey('passive_service_check_stats', $testRecord);
        $this->assertArrayHasKey('cached_host_check_stats', $testRecord);
        $this->assertArrayHasKey('cached_service_check_stats', $testRecord);
        $this->assertArrayHasKey('external_command_stats', $testRecord);
        $this->assertArrayHasKey('parallel_host_check_stats', $testRecord);
        $this->assertArrayHasKey('serial_host_check_stats', $testRecord);
        $this->assertArrayHasKey('event_profiling_enabled', $testRecord);

        $this->assertNull($data->getProgramStatus('global_service_event_handler'));
        $this->assertNull($data->getProgramStatus('global_host_event_handler'));

        $this->assertEquals('0,0,0', $data->getProgramStatus('external_command_stats'));
        $this->assertEquals('1574', $data->getProgramStatus('icinga_pid'));
    }

    public function testStatusFile2()
    {
        $statusFile = self::$dataPath . '/Data/Icinga/status.dat';
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->setStatusFile($statusFile);
        $data->load();

        $this->assertCount(43, $data->getProgramStatus());

        $data->resetState();
        $this->assertCount(0, $data->getProgramStatus());
    }

    /**
     * @expectedException TKMON\Exception\ModelException
     */
    public function testStatusFileNotExist()
    {
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->setStatusFile('/does/not/exists/123--233/status.XX');
        $data->load();
    }

    public function testUnknownSetting()
    {
        $statusFile = self::$dataPath . '/Data/Icinga/status.dat';
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->setStatusFile($statusFile);
        $data->load();

        $this->assertTrue($data->existsProgramStatus('passive_service_checks_enabled'));
        $this->assertTrue($data->existsProgramStatus('next_event_id'));
        $this->assertFalse($data->existsProgramStatus('next_event_id_XX_YY_123123'));
    }

    /**
     * @expectedException TKMON\Exception\ModelException
     */
    public function testUnknownSettingException()
    {
        $statusFile = self::$dataPath . '/Data/Icinga/status.dat';
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->setStatusFile($statusFile);
        $data->load();

        $data->getProgramStatus('next_event_id_XX_YY_123123');
    }

    public function testCreationDiff()
    {
        $statusFile = self::$dataPath . '/Data/Icinga/status.dat';
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->setStatusFile($statusFile);
        $data->load();

        $fileTimestamp = 1358868710;
        $diff = time() - $fileTimestamp;

        $this->assertEquals($diff, $data->getCreatedDiffInSeconds());
    }

    public function testDaemonRunning()
    {
        $statusFile = self::$dataPath . '/Data/Icinga/status.dat';
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->setStatusFile($statusFile);
        $data->load();

        // Okay can never test this
        $this->assertFalse($data->daemonIsRunning());
    }

    /**
     * @group integration
     */
    public function testLive1()
    {
        $data = new \TKMON\Model\Icinga\Daemon(self::$container);
        $data->load();
        $this->assertTrue($data->daemonIsRunning());
        $this->assertLessThanOrEqual(20, $data->getCreatedDiffInSeconds());
    }
}
