<?php

declare(strict_types=1);

use Foxess\CloudApi;
use Foxess\Utils;
use Foxess\Constants;
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

        $data = Utils::getErrnoMessagesList();
        outputJson("ErrnoMessageList", $data);

        $foxess->checkLogin();

        $data = $foxess->getAddressbook();
        outputJson("Addressbook", $data);


        $data = $foxess->getDeviceList();
        outputJson("Device List", $data);

        $reportVars = [
            "gridConsumption",
            "loads",
            "input",
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
        outputJson("Report monthly first entry json", $data[0]);

        $data = $foxess->getReport(
            "month",
            $reportVars
        );
        outputHtml("Report daily (reportType='month')", $data, true);
        outputCsv("Report daily", $data);

        //outputJson("Report daily first entry", $data[0]);

        $yesterday = new DateTime("yesterday", $foxess->getTZ());
        $data = $foxess->getReport(
            "day",
            $reportVars,
            $yesterday
        );
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
        $data = $foxess->getReport(
            "day",
            $rawVars,
            $yesterday
        );
        outputHtml(
            "Report Raw Variables hourly yesterday " . $yesterday->format("d.m.Y"),
            $data,
            true
        );

        $data = $foxess->getRaw(
            "hour",
            array_keys(Constants::VARIABLES)
        );
        outputHtml("Raw All Variables", $data, false);
        outputJson("Raw All Variables entry json", $data[9]);

        $now = new DateTime("now", $foxess->getTZ());
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
        $data = $foxess->getRaw("hour", $rawVars);
        outputHtml("Raw Data (hour) " . $now->format("d.m.Y H:s"), $data, false);
        outputCsv("Raw Data (hour) " . $now->format("d.m.Y H:s"), $data);
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