<?php

declare(strict_types=1);

namespace Foxess\ResultData;

use Foxess\DIContainer;

use \DateTime;
use \DateTimeZone;
/**
 * Value of a variable data entry
 */
class Value
{
    protected static DateTimeZone $tz;

    protected string $dataLabel = '';
    /**
     * Constructor
     *
     * @param array $data   Data entry of variable data
     * @param string $unit  Unit if the contained data
     */
    public function __construct(protected array $data, protected string $unit)
    {
        if (!isset(self::$tz))
            self::$tz = DIContainer::getInstance()->get("TZ");
    }
    /**
     * Returns the value of the data entry, or null if it's empty
     *
     * @return float | null
     */
    public function value(): float | null
    {
        if (empty($this->data))
            return null;
        $value = $this->data['value'];
        switch ($this->unit) {
            case 'kW':
                $value = round($value, 3);
                break;
            case '%':
                $value = round($value, 0);
                break;
            default:
                $value = round($value, 1);
        }
        return $value;
    }
    /**
     * Returns the header value of the data entry, or null if it's empty
     *
     * @return mixed
     */
    public function headerValue(): mixed
    {
        if (empty($this->data))
            return null;

        $headValue = $this->data[$this->dataLabel()];
        if ($this->dataLabel() === 'time') {
            // given date format is like "2023-08-11 12:02:57 CEST+0200"
            // The part "CEST+0200" needs to be ignored to get a correct DateTime
            $date = DateTime::createFromFormat("Y-m-d H:i:s +", $headValue, self::$tz);
            if ($date === false) {
                //Something went wrong ???
                $error = DateTime::getLastErrors();
                return $headValue;
            }
            return $date;
        }
        return $headValue;
    }

    /**
     * Returns the first key name of the data entries. 
     * This is: 
     *     "index" for the report data and indicates month, day or hour.
     *     "time" for raw data and indicates a timestamp
     *      
     * @return string|null
     */
    public function dataLabel(): string|null
    {
        if (empty($this->dataLabel))
            $this->dataLabel = array_key_first($this->data);
        return $this->dataLabel;
    }
}
