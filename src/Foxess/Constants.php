<?php declare(strict_types=1);

namespace Foxess;

/**
 * Foxess constants definitions
 */
class Constants
{
    // Your FoxEss API key
    public const CONFIG_API_KEY = 'foxess_api_key';
    //Your serialnumber for FoxEss 
    public const CONFIG_DEVICE_SN = 'foxess_device_sn';
    public const CONFIG_VARIABLES = [self::CONFIG_API_KEY,
                                     self::CONFIG_DEVICE_SN];

    public const FS_CLOUD = "https://www.foxesscloud.com";
    // Endpoints to be accessed with authentication using API key
    public const DEVICE_LIST_ENDPOINT = "/op/v0/device/list";   
    public const REALTIME_ENDPOINT = '/op/v0/device/real/query';
    public const RAW_ENDPOINT = "/op/v0/device/history/query";
    public const REPORT_ENDPOINT = "/op/v0/device/report/query";
    public const ACCESS_COUNT_ENDPOINT = "/op/v0/user/getAccessCount";
    // Enspoints to be accessed without authentication
    public const ERRNO_LIST_ENDPOINT = "/c/v0/errors/message";
    public const VARIABLE_LIST_ENDPOINT = "/op/v0/device/variable/get";
/*
    public const ERRNO_INCORRECT_INVERTER_ID = 41930;
    public const ERRNO_INVALID_DATE = 40261;
    public const ERRNO_INCORRECT_CREDENTIALS = 41807;
    public const ERRNO_TOKEN_EXPIRED = 41808;
    public const ERRNO_TOKEN_INVALID = 41809;
    public const ERROR_CODES = [
        self::ERRNO_INCORRECT_INVERTER_ID => "incorrect inverter id",
        self::ERRNO_INVALID_DATE => "invalid date",
        self::ERRNO_INCORRECT_CREDENTIALS => "wrong user name or password",
        self::ERRNO_TOKEN_EXPIRED => "token expired",
        self::ERRNO_TOKEN_INVALID => "token invalid",
    ];
    */
}

