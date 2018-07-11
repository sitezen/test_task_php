<?php
/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 06.07.2018
 * Time: 11:12
 */

namespace park;


class CarModel
{
    public $name;

    /**
     * @var  float
     * Расход топлива, л/100км, средний для данной модели, для нового автомобиля
     */
    public $fuel_consumption;

    /**
     * @var float
     * Вероятность поломки нового автомобиля данной модели в течение дня, в процентах
     */
    public $break_propability;

    /**
     * @param $model_name string Название модели
     * @param $fuel_consumption float Расход топлива, л/100км, средний для данной модели, для нового автомобиля
     * @param $break_propability float Вероятность поломки нового автомобиля данной модели в течение дня, в процентах
     */
    public function __construct($model_name, $fuel_consumption, $break_propability)
    {
        $this->name = $model_name;
        $this->fuel_consumption = $fuel_consumption;
        $this->break_propability = $break_propability;
    }
}