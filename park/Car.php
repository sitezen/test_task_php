<?php
/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 06.07.2018
 * Time: 11:27
 */

namespace park;

use park\CarModel;


class Car
{
    /**
     * @var CarModel
     */
    public $model;

    /**
     * @var int Текущий пробег (км)
     */
    public $current_mileage;

    /**
     * @var float Расход топлива с момента появления автомобиля в таксопарке (л)
     */
    public $total_fuel_consumption;

    /**
     * @var float Расход топлива конкретного автомобиля (л/100км)
     */
    public $fuel_consumption_per_100km;

    /**
     * @var Driver Сегодняшний водитель
     */
    public $today_driver = null;

    /**
     * @var int 0, если автомобиль исправен, или сколько дней осталось до окончания ремонта
     */
    public $days_till_alive = 0;

    /**
     * @var bool признак того, что начался новый день
     */
    public $day_started = false;

    /**
     * @var int Сколько дней за время службы в автопарке автомобиль провел в ремонте
     */
    public $days_in_repair = 0;

    /**
     * @var int Сколько выполнено успешных выездов
     */
    public $total_races = 0;

    /**
     * Car constructor.
     * @param \park\CarModel $model
     * @param int $current_mileage Текущий пробег автомобиля (км)
     */
    public function __construct(\park\CarModel $model, $current_mileage)
    {
        $this->model = $model;
        $this->current_mileage = $current_mileage;
        $this->fuel_consumption_per_100km = $model->fuel_consumption;
        $this->total_fuel_consumption = 0;
    }

    /** Начало нового дня
     * @param Driver|null $driver
     * @throws \Exception
     */
    public function startDay(\park\Driver $driver = null)
    {
        if($this->day_started){
            throw new \Exception('Для этого автомобиля день уже начался');
        }
        $this->day_started = true;
        if (!$this->isAlive()) {
            $this->days_till_alive--;
            // echo $this->model->name . ": осталось быть в ремонте: {$this->days_till_alive} дней <br />";
            if (!$this->isAlive()) {
                $this->days_in_repair++;
                // Ремонтом ведь не сам водитель занимается?
                if(!empty($driver)) {
                    throw new \Exception('Сегодня автомобиль неисправен, водителю требуется другой автомобиль');
                }
                return; // автомобиль всё ещё сломан, водитель не присвоен
            }
        }
        $this->today_driver = $driver;
    }

    /**
     * Завершение рабочего дня
     */
    public function endDay()
    {
        $this->today_driver = null;
        $this->day_started = false;
    }

    /**
     * @return float вероятность поломки в течение 1 дня (%)
     * @throws \Exception
     */
    public function break_propability()
    {
        $base_propability = $this->model->break_propability;
        $result = $base_propability + 1 * round($this->current_mileage / 1000); // +1% на каждые 100км
        if ($result > 70) {
            throw new \Exception('Похоже, этот автомобиль исчерпал свой ресурс');
        }
        return $result;
    }

    public function isAlive()
    {
        return $this->days_till_alive == 0;
    }

    /** Сколько заказов должен сегодня выполнить этот автомобиль
     * @return int
     */
    public function totalDayRaces()
    {
        if ($this->today_driver->is_experienced) {
            return CarPark::$racesPerDayAdv;
        } else {
            return CarPark::$racesPerDay;
        }
    }

    /** Выполнение одного заказа
     * @return bool true, если заказ успешно выполнен
     * @throws \Exception
     */
    public function doRace()
    {
        if (empty($this->today_driver)) {
            throw new \Exception('Автомобилю не присвоен водитель');
        }
        if (!$this->isAlive()) {
            throw new \Exception('Автомобиль сегодня неисправен');
        }

        /* посчитаем вероятность поломки в этой поездке, т.к. нам известна вероятность поломки в течение дня,
           значит в 1 поездке она будет меньше в среднее число поездок за день
        */

        $break_propability = $this->break_propability() / $this->totalDayRaces();

        $event = mt_rand(0, 100);

        if ($event < $break_propability) {
            // автомобиль сломался в этой поездке
            $this->days_till_alive = 3;
            $this->endDay();
            // @todo: считать ли этот день днём в ремонте?
            throw new \Exception("Автомобиль только что сломался
 (вероятность поломки в рейсе: " . round($break_propability, 2) . "%)");
        }

        // у автомобиля увеличивается пробег и израсходованное топливо:
        $this->current_mileage += CarPark::$avgMileage;
        if ($this->today_driver->is_experienced) {
            $this->total_fuel_consumption += 0.8 * ((CarPark::$avgMileage / 100) * $this->fuel_consumption_per_100km);
        }else{
            $this->total_fuel_consumption += ((CarPark::$avgMileage / 100) * $this->fuel_consumption_per_100km);
        }
        $this->total_races++;

        // у водителя увеличивается число выполненных поездок:
        $this->today_driver->addRace();

        return true;
    }

}