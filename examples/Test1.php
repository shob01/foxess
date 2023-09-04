<?php

declare(strict_types=1);

use Foxess\CloudApi;
use Foxess\ResultData\ResultDataTable;
use Foxess\Variable;
use Foxess\Value;

use Foxess\Exceptions\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
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
        //$now = new DateTime("now + 1 hour", $foxess->getTZ());
        $latestRaw = new ResultDataTable($foxess->getRaw("hour", $rawVars, $now));
        $latestData = $latestRaw->column(-1);
        if (!empty($latestData)) {
            $time = $latestData['time'];
            $latestData['time'] = $time->format('Y-m-d H:i:s');
        }

        // Read SoC (State of charge) data from today
        $socTodayData = new ResultDataTable($foxess->getRaw("day", ['SoC']));

        // Find todays minimum and maximum SoC with related timestamp, as well
        // as the latest (current) SoC and a trend -1=decreasing 0=constant 1=increasing
        $min = null;
        $max = null;
        $last = -1;
        // there is just one line of data, so refer directly to it
        $var = $socTodayData->current();
        foreach ($var as $key => $data) {
            $value = $data->value();
            if ($min === null || $value <= $min->value()) {
                $min = $data;
            }
            if ($max === null || $value > $max->value()) {
                $max = $data;
            }

            $trend = $value == $last ? 0 : ($value > $last ? 1 : -1);
            $last = $value;
        }
        // position to the very last (latest) entry
        $var->last();
        $current = $var->current()->value();

        // output values
        $socData = [
            'min' => ['value' => $min->value(), 'time' => $min->headerValue()->format('Y-m-d H:i:s')],
            'max' => ['value' => $max->value(), 'time' => $max->headerValue()->format('Y-m-d H:i:s')],
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
        echo "Exception occured: " . $fe->getMessage() . "<br>";
    }
    ?>
</body>

</html>