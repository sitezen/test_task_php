<?php
/**
 * Created by PhpStorm.
 * User: strnik
 * Date: 06.07.2018
 * Time: 13:28
 */

spl_autoload_register(
    function($className)
    {
        $className = str_replace("_", "\\", $className);
        $className = ltrim($className, '\\');
        $fileName = '';
        if ($lastNsPos = strripos($className, '\\'))
        {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        require $fileName;
    }
);

use park\CarPark;

$show_details = !empty($_GET['details']);

$park = CarPark::initFromFile(__DIR__ . DIRECTORY_SEPARATOR . 'init.json');
$park->web_output = $show_details;
$test_days = 100;

echo "В автопарке {$park->places} машиномест, " . count($park->drivers) . " водителей и "
    . count($park->cars) . " автомобилей.<br />";

for($i=0;$i<$test_days;$i++){
    $park->dayAtTheRaces();
}

echo "<hr />";
?>
<b>Итог:</b> За <?=$test_days?> дней выполнено <?=$park->totalRaces()?> заказов, израсходовано
<?=round($park->totalFuel())?>л топлива, общее число дней,
проведенных автомобилями в ремонте: <?=$park->totalDaysInRepair()?><br />
<?
echo "В среднем каждый водитель выполнил " . round($park->totalRaces()/count($park->drivers)) . " заказов <br />";
?>
<? if(!$show_details){ ?>
    <form action="index.php" method="get">
        <input type="hidden" name="details" value="1">
        <input type="submit" value="Подробнее...">
    </form>
<? } ?>
