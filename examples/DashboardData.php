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

        $reportVars = [
            "gridConsumption",
            "loads",
            "feedin",
            "generation",
            "chargeEnergyToTal",
            "dischargeEnergyToTal",
        ];

        $now = new DateTime("now", $foxess->getTZ());
        // Get production report for current month
        $monthlyReport = new ResultDataTable($foxess->getReport("year", $reportVars, $now));
        $currentMonthData = ['date' => $now->format('Y-m')] +
            $monthlyReport->column($now->format('m') - 1);

        // Get production report for today
        $dailyReport = new ResultDataTable($foxess->getReport("month", $reportVars, $now));
        $todaysData = ['date' => $now->format('Y-m-d')] +
            $dailyReport->column($now->format('d') - 1);

        // Get production report for current hour
        $hourlyReport = new ResultDataTable($foxess->getReport("day", $reportVars, $now));
        $hourData = ['date' => $now->format('Y-m-d H')] +
            $hourlyReport->column($now->format('H') - 0);

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
        // Get latest raw (real) data
        $latestRaw = new ResultDataTable($foxess->getRaw($rawVars, 'now - 10 minutes'));
        $latestData = $latestRaw->column(-1);
        if (!empty($latestData)) {
            $time = $latestData['time'];
            $latestData['time'] = $time->format('Y-m-d H:i:s');
        }

        // Read SoC (State of charge) Inverter and Battery Temperation data from today
        $rawVars = ['SoC', 'invTemperation','batTemperature'];
        $todayData = new ResultDataTable($foxess->getRaw($rawVars,'today'));
        // Calculate min, max, current and trend values for todays data
        $minMax = $todayData->getMinMax();
        $dashboardData = [
            'month' => $currentMonthData,
            'today' => $todaysData,
            'hour' => $hourData,
            'latest' => $latestData,
            'minMax' => $minMax
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