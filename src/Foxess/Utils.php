<?php declare(strict_types=1);

namespace Foxess;

use GuzzleHttp\Client;
use Foxess\Exceptions\Exception;
use Foxess\Exceptions\ErrnoException;
use Foxess\Exceptions\HttpException;
use Foxess\Requester\IRequester;
use \DateTime;
use \DateTimeZone;

class Utils
{
    // list with errno code and messages will be retrieved from API
    protected static $errno_codes = null;

    public function __construct() 
    {
        $this->init();
    }
    /**
     * Return header to be used for API requests
     */
    public static function getHeaders(): array
    {
        return [
            "User-Agent" => $_SERVER['HTTP_USER_AGENT'],
            "Accept" => "application/json, text/plain, */*",
            "lang" => "en",
            "Referer" => Constants::FS_CLOUD,
        ];
    }

    /**
     * Decode json response with error handling
     */
    public static function myJsonDecode(string $text): array
    {
        $jsonData = json_decode($text, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            //error occured
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $msg = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $msg = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $msg = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $msg = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $msg = 'Unknown error';
                    break;
            }
            throw new Exception("json decode error: $msg");
        } else if ($jsonData === null) {
            // no error, but json string contains null
            return array();
        }
        return $jsonData;
    }
    /**
     * Check for the error number returned by Foxess API and throw an
     * appropriate Exception in case error number is not equal to 0
     */
    public static function errnoToException($errno): void
    {
        if (isset(self::$errno_codes[$errno])) {
            throw new ErrnoException(self::$errno_codes[$errno], $errno);
        }
        if ($errno !== 0) {
            throw new ErrnoException("unexpected error, errno=$errno", $errno);
        }
        // just pass in case errno = 0
    }
    /**
     * Check the API response for errors and throw appropriate exceptions
     * in case an error constellation was detected.
     * If no error was detected the "result" array is returned
     */
    public static function decodeApiResponse($response): array
    {
        $res = self::myJsonDecode($response->getBody()->getContents());

        if (array_key_exists("result", $res) && $res["result"] === null && isset($res["errno"])) {
            self::errnoToException($res["errno"]);
        } else if (!isset($res["result"])) {
            throw ("missing result");
        }

        if ($response->getStatusCode() != 200) {
            $status = $response->getStatusCode();
            throw new HttpException("Http Error: Status code=$status", $status);
        }
        return $res["result"];
    }
    /**
     * Returns the list of possible errno numbers together with according error message text.
     * This request works without login, means not valid token is necessary
     *
     * @return array
     */
    public static function  getErrnoMessagesList(): array
    {
        $response = DIContainer::getInstance()->get(IRequester::class)->request(
            "GET",
            Constants::ERRNO_LIST_ENDPOINT,
            self::getHeaders(),
            ""
        );

        return self::decodeApiResponse($response);
    }
    protected function init()
    {
        $data = $this->getErrnoMessagesList();
        self::$errno_codes = $data["messages"]["en"];
        //setlocale(LC_ALL, "de_DE.UTF8");
    }
    public function dateTimeToArray(DateTime $dateTime): array
    {
        return [
            "year"   => $dateTime->format('Y'),
            "month"  => $dateTime->format('m'),
            "day"    => $dateTime->format('d'),
            "hour"   => $dateTime->format('H'),
            "minute" => $dateTime->format('i'),
            "second" => $dateTime->format('s'),
        ];
    }
}