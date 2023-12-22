<?php declare(strict_types=1);

namespace Foxess;

/**
 * Foxess constants definitions
 */
class Constants
{
    public const FS_CLOUD = "https://www.foxesscloud.com";
    public const DEVICE_LIST_ENDPOINT = Constants::FS_CLOUD . "/generic/v0/device/list";   
    public const USER_ACCESS_ENDPOINT = Constants::FS_CLOUD . "/c/v0/user/access";
    public const STATUS_ENDPOINT = Constants::FS_CLOUD . "/c/v0/plant/status/all";
    public const AUTH_ENDPOINT = Constants::FS_CLOUD . "/c/v0/user/login";
    public const DATA_ENDPOINT = Constants::FS_CLOUD . "/c/v0/device/history/raw";
    public const REPORT_ENDPOINT = Constants::FS_CLOUD . "/c/v0/device/history/report";
    public const ADDRESSBOOK_ENDPOINT = Constants::FS_CLOUD . "/c/v0/device/addressbook?deviceID=";
    public const ERRNO_LIST_ENDPOINT = Constants::FS_CLOUD . "/c/v0/errors/message";
    public const CHECK_ENDPOINT = Constants::STATUS_ENDPOINT;

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
    public const VARIABLES = [
        'batChargePower' => 'kW',
        'batCurrent' => 'A',
        'batDischargePower' => 'kW',
        'batTemperature' => '℃',
        'batVolt' => 'V',
        'boostTemperation' => '℃',
        'chargeEnergyToTal' => '',
        'chargeTemperature' => '℃',
        'dischargeEnergyToTal' => '',
        'dspTemperature' => '℃',
        'epsCurrentR' => 'A',
        'epsCurrentS' => 'A',
        'epsCurrentT' => 'A',
        'epsPower' => 'kW',
        'epsPowerR' => 'kW',
        'epsPowerS' => 'kW',
        'epsPowerT' => 'kW',
        'epsVoltR' => 'V',
        'epsVoltS' => 'V',
        'epsVoltT' => 'V',
        'feedin' => '',
        'feedin2' => '',
        'feedinPower' => 'kW',
        'generation' => '',
        'generationPower' => 'kW',
        'gridConsumption' => '',
        'gridConsumption2' => '',
        'gridConsumptionPower' => 'kW',
        'input' => '',
        'invBatCurrent' => 'A',
        'invBatPower' => 'kW',
        'invBatVolt' => 'V',
        'invTemperation' => '℃',
        'loads' => '',
        'loadsPower' => 'kW',
        'loadsPowerR' => 'kW',
        'loadsPowerS' => 'kW',
        'loadsPowerT' => 'kW',
        'meterPower' => 'kW',
        'meterPower2' => 'kW',
        'meterPowerR' => 'kW',
        'meterPowerS' => 'kW',
        'meterPowerT' => 'kW',
        'PowerFactor' => '',
        'pv1Current' => 'A',
        'pv1Power' => 'kW',
        'pv1Volt' => 'V',
        'pv2Current' => 'A',
        'pv2Power' => 'kW',
        'pv2Volt' => 'V',
        'pv3Current' => 'A',
        'pv3Power' => 'kW',
        'pv3Volt' => 'V',
        'pv4Current' => 'A',
        'pv4Power' => 'kW',
        'pv4Volt' => 'V',
        'pvPower' => 'kW',
        'RCurrent' => 'A',
        'ReactivePower' => 'kVar',
        'RFreq' => 'Hz',
        'RPower' => 'kW',
        'RVolt' => 'V',
        'SCurrent' => 'A',
        'SFreq' => 'Hz',
        'SoC' => '%',
        'SPower' => 'kW',
        'SVolt' => 'V',
        'TCurrent' => 'A',
        'TFreq' => 'Hz',
        'TPower' => 'kW',
        'TVolt' => 'V',
    ];
}

