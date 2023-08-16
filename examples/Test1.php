<?php

declare(strict_types=1);

use Foxess\CloudApi;
use Foxess\ResultData;
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

        $monthlyReport = new ResultData($foxess->getReport("year", $reportVars, $now));
        $currentMonthData = ['date' => $now->format('M Y')] +
            $monthlyReport->column($now->format('m') - 1);

        $dailyReport = new ResultData($foxess->getReport("month", $reportVars, $now));
        $todaysData = ['date' => $now->format('Y-m-d')] +
            $dailyReport->column($now->format('d') - 1);

        $hourlyReport = new ResultData($foxess->getReport("day", $reportVars, $now));
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

        $latestRaw = new ResultData($foxess->getRaw("hour", $rawVars, $now));
        $latestData = $latestRaw->column(-1);

        $socTodayData = $foxess->getRaw("day", ['SoC']);
        $data = $socTodayData[0]['data'];
        $min = 100;
        $max = 0;
        foreach ($data as $row) {
            $value = $row['value'];
            $min = $value < $min ? $value : $min;
            $max = $value > $max ? $value : $max;
        }
        $socData = [
            'min' => $min,
            'max' => $max,
            'current' => array_pop($data)['value']
        ];

        $dashboardData = [
            'month' => $currentMonthData,
            'today' => $todaysData,
            'hour' => $hourData,
            'latest' => $latestData,
            'SoC' => $socData
        ];
        outputJson('DashboardData', $dashboardData);
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