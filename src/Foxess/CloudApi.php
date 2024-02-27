<?php

declare(strict_types=1);

namespace Foxess;

use Foxess\Exceptions\Exception;
use Foxess\Requester\IRequester;
use Foxess\TokenStore\ITokenStore;
use Foxess\Config\Config;

use \DateTime;
use \DateTimeZone;

class CloudApi
{
    protected $container = null;
    protected $utils = null;
    protected $requester = null;
    protected $config = null;
    protected $tokenStore = null;
    protected $tz = null;
    protected $apiKey = null;
    protected $deviceSN = null;
    /**
     * Constructor
     *
     * @param Config $config
     * @param DateTimeZone $tz
     */
    public function __construct()
    {
        $this->container = DIContainer::getInstance();
        $this->utils = new Utils();
        $this->requester = $this->container->get(IRequester::class);
        $this->config = $this->container->get(Config::class);
        $this->tz = $this->container->get("TZ");
        $this->apiKey = $this->config->get(Constants::CONFIG_API_KEY);
        $this->deviceSN = $this->config->get(Constants::CONFIG_DEVICE_SN);
    }
    public function getTZ(): DateTimeZone
    {
        return $this->tz;
    }
    /**
     * Return header to be used for API requests.
     * Overwrite this method in case something else is needed
     */
    protected function getHeaders(): array
    {
        return $this->utils->getHeaders();
    }
    /**
     * Set special header values expected by FoxEss OpenApi for
     * authentication purposes
     *
     * @param string $path
     * @return array
     */
    protected function getSignature($path): array
    {
        $timestamp = floor(microtime(true) * 1000);
        $signature = implode('\r\n', [$path, $this->apiKey, $timestamp]);
        $signatureMd5 = md5($signature);

        return [
            'content-Type' => 'application/json',
            'token' => $this->apiKey,
            'timestamp' => strval($timestamp),
            'signature' => $signatureMd5,
        ];
    }
    /** 
     * Request data from FoxEss OpenApi
     * 
     * @return array with result data
     */
    public function request(
        string $method,
        string $path,
        array $payload = null,
        array $additionalHeaders = []
    ): array {

        $headers = $this->getHeaders();
        $headers += $this->getSignature($path);
        $headers += $additionalHeaders;
        $response = $this->requester->request(
            $method,
            Constants::FS_CLOUD . $path,
            $headers,
            $payload ? json_encode($payload) : ""
        );
        return $this->utils->decodeApiResponse($response);
    }

    /**
     * Gets a list of devices (means inverters) available in the current account
     *
     * @return array with result data
     * {
     *     "total": 1,
     *     "data": [
     *         {
     *             "deviceType": "H3-5.0-E",
     *             "hasBattery": true,
     *             "hasPV": true,
     *             "moduleSN": "KJSNKSK",
     *             "deviceSN": "MNWWFJKWFV",
     *             "productType": "H3",
     *             "stationID": "630dabf2-4770-6415-81a6-f048dfcfa05e",
     *             "status": 1
     *         }
     *     ],
     *     "pageSize": 10,
     *     "currentPage": 1
     * }
     */
    public function getDeviceList(): array
    {
        $params = [
            "pageSize" => 10,
            "currentPage" => 1,
        ];
        return $this->request('POST', Constants::DEVICE_LIST_ENDPOINT, $params);
    }

    /**
     * Reads report data from API. This data is already accumulated by day, month or year, 
     * according to given reportType
     * 
     * @param string $reportType    "day": hourly accumulated data for the requested day (24 hours)
     *                              "month": daily accumulated data for the requested month (31 days)
     *                              "year": mothly accumulated data for the requested year (12  month)
     * @param array  $variables     Array with variables to be reported
     * @param DateTime $dat         (optional) Date to be reported. Only the necessary parts of this date
     *                              will be used by API, according to the given reportType. If no
     *                              date is provided, today will be used as default
     * @return array with result data
     * {
     *     "unit": "kWh",
     *     "values": [
     *         0.40000000000009095,
     *         0.3000000000000682,
     *         0.39999999999997726,
     *         0.39999999999997726,
     *         0.3000000000000682,
     *         0.3000000000000682,
     *         0.2999999999999545,
     *         0.3000000000000682,
     *         0.10000000000002274,
     *         0.7000000000000455,
     *         0.1999999999999318,
     *         0.10000000000002274,
     *         0,
     *         0,
     *         0.10000000000002274,
     *         0,
     *         0,
     *         0.09999999999990905,
     *         0.10000000000002274,
     *         0,
     *         0,
     *         0.10000000000002274,
     *         0.39999999999997726,
     *         0.7999999999999545
     *     ],
     *     "variable": "gridConsumption"
     * },
     * {
     *     "unit": "kWh",
     *     "values": [
     *         0.2999999999999545,
     *         0.09999999999990905,
     *         0.1999999999998181,
     *         0.20000000000004547,
     *         0.20000000000004547,
     *         0.20000000000004547,
     *         0.20000000000004547,
     *         0.1999999999998181,
     *         0.20000000000004547,
     *         0.900000000000091,
     *         0.3999999999998636,
     *         0.3000000000001819,
     *         0.09999999999990905,
     *         0.20000000000004547,
     *         0.6000000000001364,
     *         0.40000000000009095,
     *         0.6000000000001364,
     *         0.7000000000000455,
     *         0.3999999999998636,
     *         0.2999999999999545,
     *         0.3999999999998636,
     *         0.3000000000001819,
     *         0.2999999999999545,
     *         0.6999999999998181
     *     ],
     *     "variable": "loads"
     * }  
     */

    public function getReport(string $reportType, array $variables, DateTime $date = null): array
    {
        if (!isset($date))
            $date = new DateTime("now", $this->getTZ());
        $params = [
            "sn" => $this->deviceSN,
            "dimension" => $reportType,
            "variables" => $variables,
        ] + $this->utils->dateTimeToArray($date,$reportType);
        return $this->request('POST', Constants::REPORT_ENDPOINT, $params);
    }
    /**
     * Reads historical raw data from API.
     * 
     * Description from FoxEss OpenApiDocument:
     * Obtain the historical data of the inverter, obtain the historical data of the 
     * last three days without specifying the time,and the time span must be less than 
     * or equal to 24 hours
     * 
     * @param array  $variables     Array with variables to be reported
     * @param string $beginDateStr  (optional) DateTime constructor string for begin date/time.
     *                              If $beginDateStr is null , data will be determined for the 
     *                              last 24 hours and $endDateStr will be ignored.
     * @param string $endDateStr   (optional) DateTime constructor string for end date/time.
     *                              If $endDateStr is not specified the 'now' will be used.
     * @return array with result data
     * {
     *     "datas": [
     *         {
     *             "unit": "kW",
     *             "data": [
     *                 {
     *                     "time": "2024-02-26 15:30:03 CET+0100",
     *                     "value": 0.478
     *                 },
     *                 {
     *                     "time": "2024-02-26 15:34:33 CET+0100",
     *                     "value": 0.478
     *                 }
     *             ],
     *             "name": "Load Power",
     *             "variable": "loadsPower"
     *         },
     *         {
     *             "unit": "%",
     *             "data": [
     *                 {
     *                     "time": "2024-02-26 15:30:03 CET+0100",
     *                     "value": 10
     *                 },
     *                 {
     *                     "time": "2024-02-26 15:34:33 CET+0100",
     *                     "value": 10
     *                 }
     *             ],
     *             "name": "SoC",
     *             "variable": "SoC"
     *         }
     *     ],
     *     "deviceSN": "ABCDEFGHIJK"
     * }
     */
    public function getRaw(array $variables, string $beginDateStr = null, string $endDateStr = 'now'): array
    {
        if (!isset($beginDateStr)) {
            $beginDate = new DateTime("now - 24 hours", $this->getTZ());
            $endDate = new DateTime("now", $this->getTZ());
        } else {
            $beginDate = new DateTime($beginDateStr, $this->getTZ());
            $endDate = new DateTime($endDateStr, $this->getTZ());
        }
        $params = [
            "sn" => $this->deviceSN,
            "variables" => $variables,
            "begin" => $beginDate->getTimestamp() * 1000,
            "end" => $endDate->getTimestamp() * 1000,
        ];
        $resultData = $this->request('POST', Constants::RAW_ENDPOINT, $params);
        return $resultData[0]['datas'];
    }
    /**
     * Reads the latest realtime data from API.
     * 
     * @param array  $variables     Array with variables to be reported
     * @return array with result data
     * {
     *      "datas": [
     *          {
     *              "unit": "kW",
     *              "name": "GridConsumption Power",
     *              "variable": "gridConsumptionPower",
     *              "value": 0.254
     *         },
     *         {
     *             "unit": "kW",
     *             "name": "Load Power",
     *             "variable": "loadsPower",
     *             "value": 0.506
     *         }
     *       ],
     *      "time": "2024-02-26 16:24:04 CET+0100",
     *      "deviceSN": "ABCDEFGHIJK"
     * }
     */
    public function getRealTime(array $variables): array
    {
        $params = [
            "sn" => $this->deviceSN,
            "variables" => $variables,
        ];
        return $this->request('POST', Constants::REALTIME_ENDPOINT, $params);
    }
    /**
     * The OpenApi is limited a certain number of accesses per day. This call
     * obtains the total and remaining accesses.
     *
     * @return array
     * {
     *     "total": "1440",
     *     "remaining": "1376"
     * }
     */
    public function getAccessCount(): array
    {
        return $this->request('GET', Constants::ACCESS_COUNT_ENDPOINT);
    }
}
