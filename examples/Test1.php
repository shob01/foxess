<?php

declare(strict_types=1);

use Foxess\CloudApi;
use Foxess\ResultData\ResultDataTable;
use Foxess\Variable;
use Foxess\Value;

use Foxess\Exceptions\Exception;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Foxess/dependencies.php';
require __DIR__ . '/helper.php';
?>
<!------------------------------------------------------------------------------>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="basic.css" type="text/css" />
    <title>Usage example script for Foxess Cloud Api classes</title>
</head>

<body>
    <?php

    try {
        $startTime = new DateTime();

        $foxess = new CloudApi();

        $foxess->checkLogin();

        $reportVars = [
            "gridConsumption",
            "loads",
            "feedin",
            "input",
            "generation",
            "chargeEnergyToTal",
            "dischargeEnergyToTal",
        ];

        $now = new DateTime("now", $foxess->getTZ());

        $monthlyReport = new ResultDataTable($foxess->getReport("year", $reportVars, $now));
        $currentMonthData = ['date' => $now->format('Y-m')] +
            $monthlyReport->column($now->format('m') - 1);

        $dailyReport = new ResultDataTable($foxess->getReport("month", $reportVars, $now));
        $todaysData = ['date' => $now->format('Y-m-d')] +
            $dailyReport->column($now->format('d') - 1);

        $hourlyReport = new ResultDataTable($foxess->getReport("day", $reportVars, $now));
        $hourData = ['date' => $now->format('Y-m-d H')] +
            $hourlyReport->column($now->format('H') - 1);


        $rawVars = [
            "gridConsumptionPower",
            "loadsPower",
            "invBatPower",
            "pv1Power",
            "pv2Power",
            "pvPower",
            "generationPower",
            "feedinPower",
            "SoC"
        ];

        $latestRaw = new ResultDataTable($foxess->getRaw("hour", $rawVars, $now));
        $latestData = ['date' => $now->format('Y-m-d H:i:s')] +
                      $latestRaw->column(-1);

        $socTodayData = new ResultDataTable($foxess->getRaw("day", ['SoC']));
        $min = null;
        $max = null;
        $last = -1;
        $var = $socTodayData->current();
        foreach($var as $key => $data) {
            $value = $data->value();
            if($min === null || $value <= $min->value()) {
                $min = $data;
            }
            if($max === null || $value >= $max->value()) {
                $max = $data;
            }

            $trend = $value == $last ? 0 : ($value > $last ? 1 : -1);
            $last = $value;
        }
        $var->last();
        $current = $var->current()->value();

        $socData = [
            'min' => [$min->value(),$min->headerValue()->format('Y-m-d H:i:s')],
            'max' => [$max->value(),$max->headerValue()->format('Y-m-d H:i:s')],
            'current' => $current,
            'trend' => $trend
        ];

        $dashboardData = [
            'month' => $currentMonthData,
            'today' => $todaysData,
            'hour' => $hourData,
            'latest' => $latestData,
            'SoC' => $socData
        ];
        $endTime = new DateTime();
        $duration = $startTime->diff($endTime);

        outputJson('DashboardData', $dashboardData);

        echo 'Time used: ' . $duration->format('%s.%f') . ' seconds' . PHP_EOL;
        
    } catch (Exception $fe) {
        $code = $fe->getCode();
        $msg = "Exception occured: " . $fe->getMessage();
        if ($code != 0)
            $msg .= " (Code=$code)";
        echo $msg . "<br>";
    }
    ?>
</body>

</html>