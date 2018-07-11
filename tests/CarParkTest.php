<?php

/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 07.07.2018
 * Time: 16:03
 */

require_once '../park/Driver.php';
require_once '../park/Car.php';
require_once '../park/CarPark.php';
require_once '../park/CarModel.php';

class CarParkTest extends PHPUnit_Framework_TestCase
{
    function testPark()
    {
        $park = new \park\CarPark(25);
        $this->assertEquals(25, $park->places);
        $driver1 = new \park\Driver(true);
        $driver2 = new \park\Driver(false);
        $driver3 = new \park\Driver(true);
        $park->addDriver($driver1);
        $park->addDriver($driver2);
        $park->addDriver($driver3);
        $this->assertEquals(3, count($park->drivers));

        $model1 = new \park\CarModel("Model 1", 10, 1.0);
        $model2 = new \park\CarModel("Model 2", 8, 0.7);
        $park->addModel($model1);
        $park->addModel($model2);

        $car1 = new \park\Car($model1, 0);
        $car2 = new \park\Car($model1, 5000);
        $car3 = new \park\Car($model2, 300);
        $park->addCar($car1);
        $park->addCar($car2);
        $park->addCar($car3);
        $this->assertEquals(3, count($park->cars));
        $this->assertEquals(0, $park->carsInRepair());

        $test_model = $park->getModelByName("Model 2");
        $this->assertSame($test_model, $model2);

        $car1->startDay($driver1);
        $car2->startDay($driver2);
        $car3->startDay($driver3);

        $was_broken = false;
        for($i=0; $i<100; $i++){
            $park->dayAtTheRaces();
            if($park->carsInRepair() > 0) $was_broken = true;
        }

        $this->assertTrue($was_broken, "Within 100 days of car park running, no one car goes broken");

        $daysForRepair = 0;
        for($i=0; $i<1000; $i++){
            /** @var \park\Car $car */
            foreach($park->cars as $car){
                if(!$car->isAlive()){
                    $car->startDay();
                    $car->endDay();
                }
            }
            $daysForRepair++;
            if(empty($park->carsInRepair())) break;
        }

        $this->assertEquals(0, $park->carsInRepair());
        $this->assertLessThan(4, $daysForRepair); // все машины за 3 дня должны быть отремонтированы

        $drivers_orders = 0;
        /** @var \park\Driver $driver */
        foreach($park->drivers as $driver){
            $drivers_orders += $driver->races_done;
        }

        $cars_orders = 0;
        /** @var \park\Car $car */
        foreach($park->cars as $car){
            $cars_orders += $car->total_races;
        }

        $this->assertGreaterThan(0, $drivers_orders);
        $this->assertGreaterThan(0, $cars_orders);
        $this->assertEquals($drivers_orders, $cars_orders);
        $this->assertEquals($drivers_orders, $park->totalRaces());

        $expectedMileage = $cars_orders * \park\CarPark::$avgMileage;
        $this->assertGreaterThan(0, $expectedMileage);

        $minFuelConsumption = 0;
        $maxFuelConsumption = 0;
        /** @var \park\Car $car */
        foreach($park->cars as $car){
            if($minFuelConsumption == 0) $minFuelConsumption = $car->fuel_consumption_per_100km;
            if($maxFuelConsumption == 0) $maxFuelConsumption = $car->fuel_consumption_per_100km;
            if($minFuelConsumption > $car->fuel_consumption_per_100km)
                $minFuelConsumption = $car->fuel_consumption_per_100km;
            if($maxFuelConsumption < $car->fuel_consumption_per_100km)
                $maxFuelConsumption = $car->fuel_consumption_per_100km;
        }

        $minFuelConsumption *= 0.8; // более экономичная езда бывалых водителей

        $this->assertGreaterThan(0,$minFuelConsumption);
        $this->assertGreaterThan(0,$maxFuelConsumption);

        /*print(" Expected mileage: " . $expectedMileage);
        print(" Total fuel: " . $park->totalFuel());
        print(" Min: " . $minFuelConsumption);
        print(" Max: " . $maxFuelConsumption);*/

        $this->assertGreaterThanOrEqual($minFuelConsumption * $expectedMileage / 100, $park->totalFuel());
        $this->assertLessThanOrEqual($maxFuelConsumption * $expectedMileage / 100, $park->totalFuel());

        // тест в конце, т.к. ожидаемое исключение прервет функцию:
        $this->setExpectedException(Exception::class);
        $park->getModelByName("Wrong name");
    }
}
