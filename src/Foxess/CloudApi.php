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
        $this->tokenStore = $this->container->get(ITokenStore::class);
        $this->tz = $this->container->get("TZ");
    }
    public function getTZ(): DateTimeZone
    {
        return $this->tz;
    }
    /**
     * Return header to be used for API requests.
     * Overwrite this method in case something else is needed
     */
    public function getHeaders(): array
    {
        return $this->utils->getHeaders();
    }
    /**
     * Checks if the token is valid
     */
    protected function checkToken(string $token): bool
    {
        $response = $this->requester->request(
            "GET",
            Constants::CHECK_ENDPOINT,
            $this->getHeaders() + ["token" => $token],
            ""
        );
        try {
            $result = $this->utils->decodeApiResponse($response);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    /**
     * Uses internal config to login at Foxess Cloud
     *
     * @return string If succesfull, the access token is returned
     */
    protected function login(): string
    {
        $auth_payload = "user=" . $this->config->getUserName() . "&password=" . $this->config->getHashedPassword();

        $response = $this->requester->request(
            "POST",
            Constants::AUTH_ENDPOINT,
            $this->getHeaders(),
            $auth_payload
        );
        $result = $this->utils->decodeApiResponse($response);

        if (!isset($result["token"]))
            throw new Exception("missing token");

        return $result["token"];
    }
    /**
     * Checks if a valid token is available, if not a new login will 
     * be executed and the returned token will be stored for future use.
     *
     * @return void
     */
    public function checkLogin(): void
    {
        $token = $this->tokenStore->get();
        if (!isset($token) || !$this->checkToken($token)) {
            $token = $this->login();
            $this->tokenStore->store($token);
        }
    }
    /**
     * Gets a list of devices (means inverters) available in the current account
     *
     * @return array with result data
     */
    public function getDeviceList(): array
    {
        $payload = [
            "pageSize" => 10,
            "currentPage" => 1,
            "total" => 0,
            "condition" => [
                "queryDate" => ["begin" => 0, "end" => 0]
            ]
        ];
        $response = $this->requester->request(
            "POST",
            Constants::DEVICE_LIST_ENDPOINT,
            $this->getHeaders() + $this->getHeaders() + ["token" => $this->tokenStore->get()],
            json_encode($payload)
        );

        return $this->utils->decodeApiResponse($response);
    }
    /**
     * Gets the address data available in the current account 
     *
     * @return array with result data
     */
    public function getAddressbook(): array
    {
        $response = $this->requester->request(
            "GET",
            Constants::ADDRESSBOOK_ENDPOINT . $this->config->getDeviceId(),
            $this->getHeaders() + $this->getHeaders() + ["token" => $this->tokenStore->get()],
            ""
        );

        return $this->utils->decodeApiResponse($response);
    }

    /**
     * Reads historical data from API.
     * 
     * @param string $reportType    "day": hourly accumulated data for the requested day (24 hours)
     *                              "month": daily accumulated data for the requested month (31 days)
     *                              "year": mothly accumulated data for the requested year (12  month)
     * @param array  $variables     Array with variables to be reported
     * @param DateTime date         (optional) Date to be reported. Only the necessary parts of this date
     *                              will be used by API, according to the given reportType. If no
     *                              date is provided, today will be used as default
     * @return array with result data
     */
    public function getReport(string $reportType, array $variables, DateTime $date = null): array
    {
        if (!isset($date))
            $date = new DateTime("now", $this->getTZ());
        $payload = [
            "deviceID" => $this->config->getDeviceId(),
            "reportType" => $reportType,
            "variables" => $variables,
            "queryDate" => $this->utils->dateTimeToArray($date)
        ];
        $response = $this->requester->request(
            "POST",
            Constants::REPORT_ENDPOINT,
            $this->getHeaders() + $this->getHeaders() + ["token" => $this->tokenStore->get()],
            json_encode($payload)
        );

        return $this->utils->decodeApiResponse($response);
    }
    /**
     * Reads raw data from API.
     * 
     * @param string $timespan      "hour"|"day"|"month"
     * @param array  $variables     Array with variables to be reported
     * @param DateTime date         (optional) DateTime to be reported. If no
     *                              date is given "now" will be used as default
     * @return array with result data
     */

    public function getRaw(string $timespan, array $variables, DateTime $dateTime = null): array
    {
        if (!isset($dateTime))
            $dateTime = new DateTime("now", $this->getTZ());
        $payload = [
            "deviceID" => $this->config->getDeviceId(),
            "variables" => $variables,
            "timespan" => $timespan,
            "beginDate" => $this->utils->dateTimeToArray($dateTime),
        ];
        $response = $this->requester->request(
            "POST",
            Constants::DATA_ENDPOINT,
            $this->getHeaders() + $this->getHeaders() + ["token" => $this->tokenStore->get()],
            json_encode($payload)
        );

        return $this->utils->decodeApiResponse($response);
    }
}
