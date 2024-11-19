# Access FoxEss Cloud Api using PHP classes

Implementation of PHP classes to access FoxEss Cloud data via FoxEss Open API. 
The official [Open API documentation](https://www.foxesscloud.com/i18n/en/OpenApiDocument.html) also mentions some [restrictions](https://www.foxesscloud.com/i18n/en/OpenApiDocument.html#4) every user should be aware of.

As of today the provided classes are restricted to single inverter installations. The simple reasons for this are that this configuration meets my personal requirements and I would not be able to test for environments with multiple inverters.  

Please be aware that this is the result of a hobby project, that I developed in my spare time.
I am a professional software developer (C/C++, Databases, ABAP OO), but this is my first bigger project using PHP. I am also
not very experienced in Web development and using github. I would really appreciate any helpful
hints and tipps regarding dos and don'ts and/or best practice.

# Install
```shell
composer require shob01/foxess
```

# Quick start

Copy the file `.env.example` to `.env` in your document root directory and fill in your 
FoxEss configuration data.
```shell
# Goto https://www.foxesscloud.com/bus/device/inverter to see your inverters SN
FOXESS_DEVICE_SN="your-inverter-serial-number"
# Goto https://www.foxesscloud.com/user/center and generate your personal API key
FOXESS_API_KEY="your-foxess-api-key"
```
Start the PHP internal web server in your document root directory:
```shell
php -S 127.0.0.1:8000
```
Open this URL in your browser: http://localhost:8000/vendor/shob01/foxess/examples/Test.php

# Usage example
This little example shows how to read production report data for current month, today and 
current hour. Additionally, todays min, max, current and trend value for SoC (State of charge), Inverter and Battery Temperation will be obtained as well.

You can find this `DashboardData.php` and some more example codes in the `examples` directory. There is also a respecting HTML file for each example code, showing the execution output.
```php
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
    // Get real time data
    // The real time data will be transformed to the same structure as the
    // the getRaw() Method returns to benefit from ResultDataTable 
    // functionality
    $realTimeRaw = new ResultDataTable($foxess->getRealtime($rawVars,true));
    $realTimeData = $realTimeRaw->column(0);
    if (!empty($realTimeData)) {
        $time = $realTimeData['time'];
        $realTimeData['time'] = $time->format('Y-m-d H:i:s');
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
        'realtime' => $realTimeData,
        'minMax' => $minMax
    ];
    $endTime = new DateTime();
    $duration = $startTime->diff($endTime);

    outputJson('DashboardData', $dashboardData);

    echo 'Time used: ' . $duration->format('%s.%f') . ' seconds' . PHP_EOL;
} catch (Exception $fe) {
    echo "Exception occured: " . $fe->getMessage() . "<br>";
}
```
Output
```json
DashboardData
{
    "month": {
        "date": "2024-11",
        "index": "11",
        "gridConsumption": {
            "value": 133.5,
            "unit": "kWh"
        },
        "loads": {
            "value": 149.3,
            "unit": "kWh"
        },
        "feedin": {
            "value": 26.9,
            "unit": "kWh"
        },
        "generation": {
            "value": 83.1,
            "unit": "kWh"
        },
        "chargeEnergyToTal": {
            "value": 36.5,
            "unit": "kWh"
        },
        "dischargeEnergyToTal": {
            "value": 47.3,
            "unit": "kWh"
        }
    },
    "today": {
        "date": "2024-11-19",
        "index": "19",
        "gridConsumption": {
            "value": 4.2,
            "unit": "kWh"
        },
        "loads": {
            "value": 2.8,
            "unit": "kWh"
        },
        "feedin": {
            "value": 0,
            "unit": "kWh"
        },
        "generation": {
            "value": 0.2,
            "unit": "kWh"
        },
        "chargeEnergyToTal": {
            "value": 0,
            "unit": "kWh"
        },
        "dischargeEnergyToTal": {
            "value": 0.2,
            "unit": "kWh"
        }
    },
    "hour": {
        "date": "2024-11-19 12",
        "index": "13",
        "gridConsumption": {
            "value": 0,
            "unit": "kWh"
        },
        "loads": {
            "value": 0,
            "unit": "kWh"
        },
        "feedin": {
            "value": 0,
            "unit": "kWh"
        },
        "generation": {
            "value": 0,
            "unit": "kWh"
        },
        "chargeEnergyToTal": {
            "value": 0,
            "unit": "kWh"
        },
        "dischargeEnergyToTal": {
            "value": 0,
            "unit": "kWh"
        }
    },
    "latest": {
        "time": "2024-11-19 12:01:03",
        "gridConsumptionPower": {
            "value": 1.629,
            "unit": "kW"
        },
        "loadsPower": {
            "value": 1.601,
            "unit": "kW"
        },
        "invBatPower": {
            "value": 0,
            "unit": "kW"
        },
        "pv1Power": {
            "value": 0.039,
            "unit": "kW"
        },
        "pv2Power": {
            "value": 0.057,
            "unit": "kW"
        },
        "pvPower": {
            "value": 0.096,
            "unit": "kW"
        },
        "generationPower": {
            "value": -0.028,
            "unit": "kW"
        },
        "feedinPower": {
            "value": 0,
            "unit": "kW"
        },
        "SoC": {
            "value": 9,
            "unit": "%"
        }
    },
    "realtime": {
        "time": "2024-11-19 12:01:03",
        "gridConsumptionPower": {
            "value": 1.629,
            "unit": "kW"
        },
        "loadsPower": {
            "value": 1.601,
            "unit": "kW"
        },
        "invBatPower": {
            "value": 0,
            "unit": "kW"
        },
        "pv1Power": {
            "value": 0.039,
            "unit": "kW"
        },
        "pv2Power": {
            "value": 0.057,
            "unit": "kW"
        },
        "pvPower": {
            "value": 0.096,
            "unit": "kW"
        },
        "generationPower": {
            "value": -0.028,
            "unit": "kW"
        },
        "feedinPower": {
            "value": 0,
            "unit": "kW"
        },
        "SoC": {
            "value": 9,
            "unit": "%"
        }
    },
    "minMax": {
        "SoC": {
            "unit": "%",
            "min": {
                "value": 9,
                "time": "2024-11-19 06:36:54"
            },
            "max": {
                "value": 13,
                "time": "2024-11-19 00:00:41"
            },
            "current": 9,
            "trend": 0
        },
        "invTemperation": {
            "unit": "\u2103",
            "min": {
                "value": 31.2,
                "time": "2024-11-19 06:14:23"
            },
            "max": {
                "value": 33.8,
                "time": "2024-11-19 12:01:03"
            },
            "current": 33.8,
            "trend": 0
        },
        "batTemperature": {
            "unit": "\u2103",
            "min": {
                "value": 27.9,
                "time": "2024-11-19 08:51:58"
            },
            "max": {
                "value": 29,
                "time": "2024-11-19 01:03:43"
            },
            "current": 27.9,
            "trend": 0
        }
    }
}
Time used: 3.909128 seconds
```
# Configuration / Dependency Injection Container
There are serveral options to configure how things are done. I implemented a very simple 
DI Container that is used to control configurable dependencies. The DI Container is setup in the 
PHP code `dependencies.php`
```php
$container = DIContainer::getInstance();

//$container->set(Config::class,fn() => new ConfigFile(__DIR__ . "/../../foxess_config.json"));
$container->set(Config::class,fn() => new ConfigDotEnv());
$container->set(IRequester::class,fn() => new GuzzleHttpRequester());
$container->set('TZ',fn() => new DateTimeZone("Europe/Berlin"));
``````
- `Config::class` defines where the FoxEss configuration data is coming from. 
    There are already two ready implemented classes available:

    - `ConfigDotEnv`: (default) reads the data from a .env file and makes the entries available as local 
        environment variables.
    - `ConfigFile`: reads the data from a json file.
    <p/>
- `IRequester::class` defines Request and Response handling. I am using GuzzleHttp, but this can be replaced 
    by anything that is implementing `\Psr\Http\Message\ResponseInterface`. Just implement your own version 
    of `IRequester` interface.

- `TZ` defines the timezone that is used for requesting data and also in some output features like 
    `HtmlTableDataFormatter` and `ResultDataTable`class. See [W3 schools PHP Supported Timezones](https://www.w3schools.com/php/php_ref_timezones.asp) for details.

 