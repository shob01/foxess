# Access FoxEss Cloud Api using PHP classes

Implementation of PHP classes to access FoxEss Cloud data via FoxEss Cloud Api. 
Unfortunately, I didn't find any helpful FoxEss Api Documentation, so I gathered 
everything I need to know by studying different FoxEss API implementations here on github. 
One of the really helpful implementation was https://github.com/mhzawadi/foxess-mqtt. 
But, as I am not using HomeAssistant, I needed something else. 

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
# If you want to use .env file for configuration you can use this file as a template.
# Provide your variables and rename this file to .env in the document root directory.
USERNAME="your-username"
# You can use the site https://md5.cz, or similar to create the md5 hashed password
HASHED_PASSWORD="your-md5-hashed-password"
# You can grab the device id from the URL when viewing inverter details in 
# FoxEss cloud:
# https://www.foxesscloud.com/bus/device/inverterDetail?id=<your-device-id>&flowType= ...
DEVICE_ID="your-device-id"
```
Start the PHP internal web server in your document root directory:
```shell
php -S 127.0.0.1:8000
```
Open this URL in your browser: http://localhost:8000/vendor/shob01/foxess/examples/Test.php

# Usage example
This little example shows how to read todays SoC (State of charge) data and determin min, max current 
and trend values
```php
try {
    $foxess = new CloudApi();
    $foxess->checkLogin();

    // Read SoC (State of charge) data from today
    $socTodayData = new ResultDataTable($foxess->getRaw("day", ['SoC']));

    // Find todays minimum and maximum SoC with related timestamp, as well
    // as the latest (current) SoC and a trend -1=decreasing 0=constant 1=increasing
    $min = null;
    $max = null;
    $last = -1;
    // there is just one line of data, so refer directly to it
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
    // position to the very last (latest) entry
    $var->last();
    $current = $var->current()->value();

    // output values
    $socData = [
        'min' => [$min->value(),$min->headerValue()->format('Y-m-d H:i:s')],
        'max' => [$max->value(),$max->headerValue()->format('Y-m-d H:i:s')],
        'current' => $current,
        'trend' => $trend
    ];
    echo "<pre>" . json_encode($socData, JSON_PRETTY_PRINT) . "</pre>";

} catch (Exception $fe) {
    $code = $fe->getCode();
    $msg = "Exception occured: " . $fe->getMessage();
    if ($code != 0)
        $msg .= " (Code=$code)";
    echo $msg . "<br>";
}
```
Output
```json
{
    "min": [
        9,
        "2023-08-26 07:47:38"
    ],
    "max": [
        100,
        "2023-08-26 12:35:48"
    ],
    "current": 98,
    "trend": 0
}
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
$container->set(ITokenStore::class,fn() => new SessionTokenStore());
$container->set('TZ',fn() => new DateTimeZone("Europe/Berlin"));
``````
- `Config::class` defines where the FoxEss configuration data is coming from. 
    There are already two ready implemented classes available:

    - `ConfigDotEnv`: (default) reads the data from a .env file and makes the entries available as local 
        environment variables
    - `ConfigFile`: reads the data from a json file
    <p/>
- `IRequester::class` defines Request and Response handling. I am using GuzzleHttp, but this can be replaced 
    by anything that is implementing `\Psr\Http\Message\ResponseInterface`. Just implement your own version 
    of `IRequester` interface

- `ITokenStore::class` defines how the FoxEss access token is read and stored. The access token will be 
    provied by FoxEss API on login and is necessary for subsequent API calls. My default implementation is 
    using session variables, but this can be replaced by your own implementation of the `ITokenStore` interface

- `TZ` defines the timezone that is used for requesting data and also in some output features like 
    `HtmlTableDataFormatter` class. See [PHP manual](https://www.php.net/manual/en/timezones.php) for details.

 