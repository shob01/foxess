<?php

declare(strict_types=1);

use Foxess\CloudApi;
use Foxess\Utils;
use Foxess\Constants;
use Foxess\Exceptions\Exception;
use Foxess\Exceptions\ErrnoException;

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

        $data = Utils::getErrnoMessagesList();
        $msgEn = $data["messages"]["en"];
        ksort($msgEn);
        outputJson("ErrnoMessageList", $msgEn);

        $accessCountStart = $foxess->getAccessCount();
        outputJson("Access Count", $accessCountStart);

        try {
            // Get Device List fails randomly with "Bad Gateway" or "Server exception" 
            // But sometimes it works fine ...
            // So give it an extra try  ... catch block
            echo 'Get device list ...<br>' . PHP_EOL;
            $data = $foxess->getDeviceList();
            outputJson("Device List", $data);
        } catch (Exception $fe) {
            echo "Exception occured: " . $fe->getMessage() . "<br>";
        }

        $reportVars = [
            "gridConsumption",
            "loads",
            "feedin",
            "generation",
            "chargeEnergyToTal",
            "dischargeEnergyToTal",
        ];

        $data = $foxess->getReport(
            "year",
            $reportVars
        );
        outputHtml("Report monthly (reportType='year')", $data, true);
        outputJson("Report monthly first entry JSON", $data[0]);

        $data = $foxess->getReport(
            "month",
            $reportVars
        );
        outputHtml("Report daily (reportType='month')", $data, true);
        outputCsv("Report daily CSV", $data);

        //outputJson("Report daily first entry", $data[0]);

        $now = new DateTime("now", $foxess->getTZ());
        $data = $foxess->getReport("day", $reportVars, $now);
        outputHtml(
            "Report hourly now (reportType='day')" . $now->format("d.m.Y"),
            $data,
            true
        );
        $yesterday = new DateTime("yesterday", $foxess->getTZ());
        $data = $foxess->getReport("day", $reportVars, $yesterday);
        outputHtml(
            "Report hourly yesterday (reportType='day')" . $yesterday->format("d.m.Y"),
            $data,
            true
        );

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

        //$data = $foxess->getRaw(array_keys(Constants::VARIABLES));
        //outputHtml("Raw All Variables", $data, false);
        //outputJson("Raw All Variables entry json", $data[9]);

        //$now = new DateTime("now", $foxess->getTZ());
        $data = $foxess->getRaw($rawVars, 'now - 1 hours');
        outputHtml("Raw Data (last hour) ", $data, false);
        outputCsv("Raw Data (last hour) CSV", $data);
        outputJson("Raw Data (last hour) first entry JSON", $data[0]);

        // Get realtime data
        $data = $foxess->getRealtime($rawVars);
        outputJson("Real Time Data JSON", $data);
        // Get realtime data in raw data structure
        $data = $foxess->getRealtime($rawVars,true);
        outputJson("Real Time Data in raw format JSON", $data);
        outputHtml("Real Time Data in raw format HTML", $data,false);

        $data = $foxess->getRaw([], 'now - 1 hours');
        outputHtml("Raw Data All Variables (last hour) ", $data, false);

        $endTime = new DateTime();
        $duration = $startTime->diff($endTime);

        $accessCountEnd = $foxess->getAccessCount();
        echo '<br><h3>API calls used: ' . $accessCountStart['remaining'] - $accessCountEnd['remaining'] . '</h3>';

        echo '<h3>Time used: ' . $duration->format('%s.%f') . ' seconds' . '</h3>';
    } catch (Exception $fe) {
        echo "Exception occured: " . $fe->getMessage() . "<br>";
    }
    ?>
</body>

</html>