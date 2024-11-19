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
    protected string $dataLabel = '';
    /**
     * Constructor
     *
     * @param array $data   Data entry of variable data
     * @param string $unit  Unit if the contained data
     */
    public function __construct(
        protected mixed $data,
        protected $dataKey,
        protected string $unit
    ) {
    }
    /**
     * Returns the value of the data entry, or null if it's empty
     *
     * @return float | null
     */
    public function value(): float | null
    {
        $value = $this->dataKey === 'value' ? $this->data['value'] : $this->data;
        if (gettype($value) === 'string')
            return 0;
        switch ($this->unit) {
            case 'kW':
                $value = round($value, 3);
                break;
            case '%':
            case '':
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
        $headValue = $this->dataKey === 'value' ? $this->data[$this->dataLabel()] : $this->dataKey;
        if ($this->dataLabel() === 'time') {
            // given date format is like "2023-08-11 12:02:57 CET+0200"
            try {
                $date = new DateTime($headValue);
            } catch (\Exception $ex) {
                //Something went wrong ??? 
                throw $ex;
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
            $this->dataLabel = $this->dataKey === 'value' ? 'time' : 'index';
        return $this->dataLabel;
    }
}
