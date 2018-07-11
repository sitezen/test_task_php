<?php

/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 07.07.2018
 * Time: 15:01
 */

require_once '../park/Driver.php';
require_once '../park/Car.php';
require_once '../park/CarPark.php';
require_once '../park/CarModel.php';


class CarTest extends PHPUnit_Framework_TestCase
{
    function testCreate()
    {
        $car_model = new \park\CarModel('test name', 10, 1.3);
        $this->assertEquals(10, $car_model->fuel_consumption);
        $this->assertEquals('test name', $car_model->name);
        $this->assertEquals(1.3, $car_model->break_propability);
    }
}
