<?php

require_once '../park/Driver.php';

class DriverTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $driver = new \park\Driver(true);
        $this->assertEquals(0, $driver->races_done);
        $this->assertTrue($driver->is_experienced);
    }

    public function testBecomePro()
    {
        $driver = new \park\Driver(false);
        $this->assertFalse($driver->is_experienced);
        for ($i = 0; $i < 100; $i++) {
            $driver->addRace();
        }
        $this->assertEquals(100, $driver->races_done);
        $this->assertTrue($driver->is_experienced);
    }
}
?>