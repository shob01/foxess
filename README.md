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
```
Output
```json
DashboardData
{
    "month": {
        "date": "2024-02",
        "index": "2",
        "gridConsumption": 198.7,
        "loads": 230.8,
        "feedin": 15,
        "generation": 103.5,
        "chargeEnergyToTal": 50.5,
        "dischargeEnergyToTal": 68.4
    },
    "today": {
        "date": "2024-02-27",
        "index": "27",
        "gridConsumption": 3,
        "loads": 2.1,
        "feedin": 0.1,
        "generation": 0.3,
        "chargeEnergyToTal": 0,
        "dischargeEnergyToTal": 0.2
    },
    "hour": {
        "date": "2024-02-27 09",
        "index": "10",
        "gridConsumption": 0.2,
        "loads": 0.4,
        "feedin": 0,
        "generation": 0.2,
        "chargeEnergyToTal": 0,
        "dischargeEnergyToTal": 0.1
    },
    "latest": {
        "time": "2024-02-27 09:57:36",
        "gridConsumptionPower": 1.108,
        "loadsPower": 1.443,
        "invBatPower": 0,
        "pv1Power": 0.219,
        "pv2Power": 0.226,
        "pvPower": 0.445,
        "generationPower": 0.335,
        "feedinPower": 0,
        "SoC": 10
    },
    "minMax": {
        "SoC": {
            "unit": "%",
            "min": {
                "value": 10,
                "time": "2024-02-27 01:15:21"
            },
            "max": {
                "value": 11,
                "time": "2024-02-27 09:48:36"
            },
            "current": 10,
            "trend": 0
        },
        "invTemperation": {
            "unit": "\u2103",
            "min": {
                "value": 30,
                "time": "2024-02-27 05:00:28"
            },
            "max": {
                "value": 34,
                "time": "2024-02-27 09:57:36"
            },
            "current": 34,
            "trend": 1
        },
        "batTemperature": {
            "unit": "\u2103",
            "min": {
                "value": 26.6,
                "time": "2024-02-27 07:20:01"
            },
            "max": {
                "value": 26.9,
                "time": "2024-02-27 02:54:24"
            },
            "current": 26.6,
            "trend": -1
        }
    }
}
Time used: 8.49056 seconds
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

 