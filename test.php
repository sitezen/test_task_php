<?php
/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 06.07.2018
 * Time: 21:33
 * Тесты без phpUnit
 */

spl_autoload_register(
    function ($className) {
        $className = str_replace("_", "\\", $className);
        $className = ltrim($className, '\\');
        $fileName = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        require $fileName;
    }
);

use park\CarPark;
use park\Driver;
use park\Car;
use park\CarModel;

$park = new CarPark(30);
$park->web_output = true;
$driver = new Driver(false);
for ($i = 0; $i < 100; $i++) {
    $driver->addRace();
}

if ($driver->races_done !== 100) throw new \Exception('Число поездок водителя вычисляется неверно');
if (!$driver->is_experienced && Driver::RACES_TO_BE_EXPERIENCED < 100) throw new \Exception('Водитель должен стать Pro');

$driver->races_done = 0;

$car_model = new CarModel('test_name', 100, 1);
$park->addModel($car_model);

$check_ex = false;
try {
    $wrong_model = $park->getModelByName('wrong_model');
} catch (\Exception $e) {
    $check_ex = true;
}

if (!$check_ex) throw new \Exception('Метод park->getModelByName работает неверно');

$car = new Car($car_model, 0);
$park->addCar($car);
$park->addDriver($driver);

$car->startDay($driver);

$check_ex = false;
$orders_done = 0;
try {
    for ($i = 0; $i < 10000; $i++) {
        $car->doRace();
        $orders_done++;
    }
} catch (\Exception $e) {
    $check_ex = true;
}
if (!$check_ex) throw new \Exception('Ошибка в классе Car, 10 000 выездов не могут пройти без поломок');

$days_repair = 0;
for ($i = 0; $i < 100; $i++) {
    $car->startDay();
    $days_repair++;
    $car->endDay();
    if ($car->isAlive()) break;
}

if ($days_repair !== 3) {
    throw new \Exception("Car: автомобиль в ремонте должен находиться 3 дня, результат теста: $days_repair");
}

$expected_mileage = $car->total_races * CarPark::$avgMileage;
if ($car->current_mileage !== $expected_mileage) {
    throw new \Exception("Car: неверный пробег, ожидалось $expected_mileage, получено {$car->current_mileage}");
}

if($driver->races_done !== $park->totalRaces()){
    throw new \Exception("Неверно вычислено число выполненных заказов (1 водитель, 1 автомобиль)"
        . " (у единственного водителя: {$driver->races_done}, в целом в таксопарке: {$park->totalRaces()}="
        . "{$car->total_races}, единственный автомобиль выполнил $orders_done заказов)");
}


$car2 = new Car($car_model, 0);
$park->addCar($car2);

$car2->startDay($driver);

$check_ex = false;
try {
    for ($i = 0; $i < 10000; $i++) {
        $car2->doRace();
    }
} catch (\Exception $e) {
    $check_ex = true;
}
if (!$check_ex) throw new \Exception('Ошибка в классе Car, 10 000 выездов не могут пройти без поломок');

$avgMileage = CarPark::$avgMileage;
if ($avgMileage <= 0) {
    throw new \Exception('Неверное значение среднего пробега за один вызов: ' . $avgMileage);
}

$expected_total_mileage = $car->total_races * CarPark::$avgMileage + $car2->total_races * CarPark::$avgMileage;

if ($expected_total_mileage != $park->totalRaces() * CarPark::$avgMileage)
    throw new \Exception("Неверно вычислен общий пробег, ожидавшееся значение {$park->totalRaces()}*$avgMileage="
        . "{$car->total_races}*{$avgMileage}+{$car2->total_races}*$avgMileage"
    );

$car->startDay($driver);
$car2->startDay(); // эта машина сейчас неисправна
if(count($park->workingCars()) !== 1){
    throw new \Exception("Неверно вычислено число исправных автомобилей на конец тестов: " . count($park->workingCars()));
}

if($driver->races_done !== $park->totalRaces()){
    throw new \Exception("Неверно вычислено число выполненных заказов"
    . " (у единственного водителя: {$driver->races_done}, в целом в таксопарке: {$park->totalRaces()}="
    . "{$car->total_races}+{$car2->total_races})");
}

$park->log("Все тесты успешно завершены!");