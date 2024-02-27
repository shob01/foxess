<?php

declare(strict_types=1);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT'], '.env');
$dotenv->load();

testOpenApi();

///////////////////////////////////////////////////////////////////////////
function getSignature($token, $path, $lang = 'en')
{
    $timestamp = floor(microtime(true) * 1000);
    $signature = implode('\r\n', [$path, $token, $timestamp]);
    $signatureMd5 = md5($signature);

    return [
        'Content-Type' => 'application/json',
        'Token' => $token,
        'Timestamp' => strval($timestamp),
        'Lang' => $lang,
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
        'Signature' => $signatureMd5,
    ];
}
///////////////////////////////////////////////////////////////////////////
function request($method, $path, $param = null)
{

    $debug = false;
    $sleepTime = 0;
    $domain = 'https://www.foxesscloud.com';
    $api_key = $_ENV['FOXESS_API_KEY'];

    $url = $domain . $path;
    $headers = getSignature($api_key, $path);

    // Convert headers array to the format expected by cURL
    $headerList = [];
    foreach ($headers as $key => $value) {
        $headerList[] = "$key: $value";
    }

    if ($sleepTime > 0) {
        sleep($sleepTime);
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerList);

    switch ($method) {
        case 'get':
            curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($param));
            break;
        case 'post':
            $paramJson = json_encode($param);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $paramJson);
            break;
        default:
            throw new Exception('Request method error');
    }

    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($debug) {
        echo "URL: $url<br>";
        echo "Method: $method<br>";
        echo "Param: " . print_r($param, true) . "<br>";
        echo "Headers: " . print_r($headerList, true) . "<br>";
        echo "HTTP-Code: $status<br>";
        echo "Response: $response<br>";
        echo str_repeat('-------------------------', 5) . "<br>";
    }

    curl_close($curl);
    return json_decode($response, true);
}
///////////////////////////////////////////////////////////////////////////
function testOpenApi()
{
    //=============================================================
    $path = '/op/v0/device/real/query';
    $rawVars = [
        "gridConsumptionPower",
        "loadsPower",
        "invBatPower",
        "pv1Power",
        "pv2Power",
        "pvPower",
        "generationPower",
        "SoC"
    ];
    $params = [
        //"sn" => $_ENV['FOXESS_DEVICE_SN'],
        "variables" => $rawVars,
    ];
    //$params = [];
    echo "<pre>$path</pre>";
    echo "<pre>" . json_encode($params, JSON_PRETTY_PRINT) . "</pre>";
    $response = request('post', $path, $params);
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    //echo '<pre>'; print_r($response); echo '</pre>';
    echo str_repeat('-------------------------', 5) . "<br>";
    //=============================================================
    $path = '/op/v0/device/history/query';
    $rawVars = [
        "loadsPower",
        "PowerFactor",
        "TCurrent",
        "TVolt",
        "TFreq",
        "SoC"
    ];
    $tz = new DateTimeZone("Europe/Berlin");
    $now = new DateTime("now", $tz);
    $begin = new DateTime("now - 20 minutes", $tz);

    $params = [
        "sn" => $_ENV['FOXESS_DEVICE_SN'],
        "variables" => $rawVars,
        "begin" => $begin->getTimestamp() * 1000,
        "end" => $now->getTimestamp() * 1000
    ];
    //$params = [];
    echo "<pre>$path</pre>";
    echo "<pre>" . json_encode($params, JSON_PRETTY_PRINT) . "</pre>";
    $response = request('post', $path, $params);
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    echo str_repeat('-------------------------', 5) . "<br>";
    //=============================================================
    $path = '/op/v0/device/report/query';
    $reportVars = [
        "gridConsumption",
        "loads",
        "feedin",
        "generation",
        "chargeEnergyToTal",
        "dischargeEnergyToTal",
    ];
    $params = [
        "sn" => $_ENV['FOXESS_DEVICE_SN'],
        "year" => '2024',
        "month" => '02',
        "day" => '24',
        "dimension" => "day",
        "variables" => $reportVars
    ];
    echo "<pre>$path</pre>";
    echo "<pre>" . json_encode($params, JSON_PRETTY_PRINT) . "</pre>";
    $response = request('post', $path, $params);
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    echo str_repeat('-------------------------', 5) . "<br>";

    //=============================================================
    $path = '/op/v0/device/list';
    $params = [
        "currentPage" => 1,
        "pageSize" => 10
    ];
    echo "<pre>$path</pre>";
    echo "<pre>" . json_encode($params, JSON_PRETTY_PRINT) . "</pre>";
    $response = request('post', $path, $params);
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    //=============================================================
    $path = '/op/v0/user/getAccessCount';
    $params = [
        "currentPage" => 1,
        "pageSize" => 10
    ];
    echo "<pre>$path</pre>";
    echo "<pre>" . json_encode($params, JSON_PRETTY_PRINT) . "</pre>";
    $response = request('get', $path, $params);
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
}
