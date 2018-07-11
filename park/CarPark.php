<?php
/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 06.07.2018
 * Time: 12:19
 */

namespace park;


class CarPark
{
    /**
     * @var int среднее число поездок, совершаемое за день одним автомобилем с обычным водителем
     */
    public static $racesPerDay = 10;

    /**
     * @var int среднее число поездок, совершаемое за день одним автомобилем с "бывалым" водителем
     */
    public static $racesPerDayAdv = 12;

    /**
     * @var int средняя длина пути при одном заказе (км)
     * (предположим, "бывалый" водитель всё же проезжает столько же, но тратит меньше топлива из-за монеры вождения)
     */
    public static $avgMileage = 7;

    /**
     * @var int количество машиномест
     */
    public $places;

    /**
     * @var array текущие водители в парке
     */
    public $drivers = [];

    /** модели автомобилей в парке
     * @var array
     */
    public $models = [];

    /**
     * @var array все автомобили парка
     */
    public $cars = [];

    /**
     * @var bool Выводить ли информацию для веб-страницы
     */
    public $web_output = false;

    /**
     * @var int Дней работы таксопарка
     */
    public $days_running = 0;

    /**
     * CarPark constructor.
     * @param int $places
     */
    public function __construct($places)
    {
        $this->places = $places;
    }

    /**
     * @param Driver $driver
     */
    public function addDriver(Driver $driver)
    {
        $this->drivers[] = $driver;
    }

    /**
     * @param Car $car
     */
    public function addCar(Car $car)
    {
        if (count($this->cars) >= $this->places) {
            throw new \Exception('Число автомобилей превышает число машиномест таксопарка');
            // можно конечно предположить, что в среднем половина машин всё время находится в поездках,
            // и тогда машин может быть в 2 раза больше, чем мест, и тогда можно играться с ситуацией
            // "столько-то мест занято неисправными автомобилями", но как-то не выглядит это реальным
        }
        $this->cars[] = $car;
    }

    /**
     * @param CarModel $model
     */
    public function addModel(CarModel $model)
    {
        $this->models[] = $model;
    }

    /** Возвращает модель автомобиля по названию модели
     * @param $name
     * @return CarModel
     * @throws \Exception
     */
    public function getModelByName($name)
    {
        /** @var CarModel $carModel */
        foreach ($this->models as $carModel) {
            if ($carModel->name == $name) return $carModel;
        }
        throw new \Exception("В таксопарке нет модели автомобиля '{$name}'");
    }

    /** Машины, которые сегодня на линии (внутри дня)
     * @return array
     */
    public function workingCars()
    {
        $result = [];
        /** @var Car $car */
        foreach ($this->cars as $car) {
            if ($car->isAlive() && !empty($car->today_driver)) $result[] = $car;
        }
        return $result;
    }

    public function log($msg)
    {
        if ($this->web_output) echo $msg . '<br />';
    }

    /** Всего выполнено поездок автомобилями таксопарка
     * @return int
     */
    public function totalRaces()
    {
        $r = 0;
        /** @var Car $car */
        foreach ($this->cars as $car) {
            $r += $car->total_races;
        }
        return $r;
    }

    /** Всего израсходовано топлива автомобилями таксопарка
     * @return float
     */
    public function totalFuel()
    {
        $r = 0.0;
        /** @var Car $car */
        foreach ($this->cars as $car) {
            $r += $car->total_fuel_consumption;
        }
        return $r;
    }

    /** Сколько дней машина провела в ремонте
     * @return int
     */
    public function totalDaysInRepair()
    {
        $r = 0;
        /** @var Car $car */
        foreach ($this->cars as $car) {
            $r += $car->days_in_repair;
        }
        return $r;
    }

    /** Количество машин в ремонте в данный момент
     * @return int
     */
    public function carsInRepair()
    {
        $r = 0;
        /** @var Car $car */
        foreach($this->cars as $car){
            if(!$car->isAlive()) $r++;
        }
        return $r;
    }

    /**
     * Один день из жизни таксопарка...
     * Наш таксопарк грамотно рекламируется, клиенты звонят не переставая, и мы выполняем в день столько заказов,
     * сколько позволяет количество водителей/автомобилей
     */
    public function dayAtTheRaces()
    {
        $this->days_running++;
        $this->log("<hr />День {$this->days_running}:");
        $drivers = $this->drivers;
        shuffle($drivers);

        $start_fuel = $this->totalFuel();
        $start_orders = $this->totalRaces();

        // присвоение машинам водителей на сегодня, в случайном порядке:
        /** @var Driver $driver */
        foreach ($drivers as $driver) {
            /** @var Car $car */
            foreach ($this->cars as $car) {
                if ($car->day_started) continue;
                try {
                    $car->startDay($driver);
                    break;
                } catch (\Exception $e) {
                    // машина сегодня неисправна, водителю достанется другая
                    continue;
                }
            }
        }

        /** @var Car $car */
        foreach($this->cars as $car){
            if(!$car->day_started) $car->startDay();
        }

        /* Каждая машина выполняет заказы сегодняшнего дня. Если машина сломалась, водитель идёт домой до завтра,
          вместо того, чтобы пересесть на другую машину, если есть простаивающие */
        /** @var Car $car */
        foreach ($this->workingCars() as $car) {
            try {
                /*$this->log("Заказов в день " . $car->totalDayRaces() . "/ " . $car->today_driver->fio
                    . $car->today_driver->is_experienced);*/
                for ($i = 1; $i <= $car->totalDayRaces(); $i++) {
                    $car->doRace();
                }
            } catch (\Exception $e) {
                // машина сломалась, водитель ушел домой
                if ($this->web_output)
                    $this->log($e->getMessage()
                        . " ({$car->model->name} с пробегом {$car->current_mileage}км)");
            }
        }

        foreach ($this->cars as $car) {
            $car->endDay();
        }

        $day_fuel = round($this->totalFuel() - $start_fuel);
        $day_orders = round($this->totalRaces() - $start_orders);

        $this->log("Выполнено заказов за день: {$day_orders}, израсходовано топлива: {$day_fuel}л");
        $this->log("Автомобилей в ремонте: " . $this->carsInRepair());
    }

    /** Инициализация таксопарка по данным из JSON файла
     * @param $filepath
     * @return CarPark
     * @throws \Exception
     */
    public static function initFromFile($filepath)
    {
        $init_json = file_get_contents($filepath);
        $init_data = json_decode($init_json);

        $park = new CarPark($init_data->park->places);

// модели автомобилей:
        foreach ($init_data->models as $m) {
            $model = new \park\CarModel($m->name, $m->fuel_consumption, $m->break_propability);
            $park->addModel($model);
        }

// добавляем водителей таксопарка:
        foreach ($init_data->drivers as $key => $drv) {
            $driver = new \park\Driver($drv->type == "pro", 0, "driver" . ($key + 1));
            $park->addDriver($driver);
        }

// создаём автомобили:
        foreach ($init_data->cars as $the_car) {
            $car = new \park\Car($park->getModelByName($the_car->brand), $the_car->km);
            $park->addCar($car);
        }

        return $park;
    }
}