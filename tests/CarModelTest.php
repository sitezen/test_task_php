<?php

/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 07.07.2018
 * Time: 15:09
 */

require_once "../park/CarModel.php";

class CarModelTest extends PHPUnit_Framework_TestCase
{
    function testCreate()
    {
        $car_model = new \park\CarModel("Test name", 10, 1.0);
        $this->assertEquals("Test name", $car_model->name);
        $this->assertEquals(10, $car_model->fuel_consumption);
        $this->assertEquals(1, $car_model->break_propability);
    }
}
