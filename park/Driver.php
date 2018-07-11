<?php
/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 06.07.2018
 * Time: 11:44
 */

namespace park;


class Driver
{
    /**
     * @var string Id водителя
     */
    public $fio;

    /**
     * @var bool Бывалый водитель
     */
    public $is_experienced;

    /**
     * @var int Количество поездок, которое водитель выполнил в этом таксопарке
     */
    public $races_done;

    /**
     * Сколько поездок должен выполнить начинающий водитель в данном таксопарке, чтобы в его сознании произошел квантовый
     * скачок и он стал "бывалым"
     */
    const RACES_TO_BE_EXPERIENCED = 90;

    /**
     * Driver constructor.
     * @param bool $is_experienced
     * @param int $races_done
     * @param string $fio
     */
    public function __construct($is_experienced, $races_done = 0, $fio = null)
    {
        $this->fio = $fio;
        $this->is_experienced = $is_experienced;
        $this->races_done = $races_done;
    }

    public function addRace()
    {
        $this->races_done++;
        if(!$this->is_experienced && $this->races_done > self::RACES_TO_BE_EXPERIENCED){
            $this->is_experienced = true;
        }
    }
}