<?php

declare(strict_types=1);

namespace Foxess\ResultData;

use Foxess\Exceptions\Exception;
use \Iterator;

/**
 * Simple Iterator class for Foxess API report and raw data 
 */
class ResultDataTable implements Iterator
{
    protected int $rowIndex = 0;
    /**
     * Constructor
     *
     * @param array $resultData   data array returned from either CloudApi class methods
     *                            getReport() or getRaw()
     */
    public function __construct(protected array $resultData)
    {
    }
    /**
     * Iterator implementation
     *
     * @return Variable
     */
    public function current(): Variable|null
    {
        if (!$this->valid())
            return null;
        return new Variable($this->resultData[$this->rowIndex]);
    }
    /**
     * Iterator implementation
     *
     * @return integer
     */
    public function key(): int
    {
        return $this->rowIndex;
    }
    /**
     * Iterator implementation
     *
     * @return void
     */
    public function next(): void
    {
        $this->rowIndex++;
    }
    /**
     * Iterator implementation
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->rowIndex = 0;
    }
    /**
     * Iterator implementation
     *
     * @return boolean
     */
    public function valid(): bool
    {
        // count() indicates how many items are in the list
        return $this->rowIndex < count($this->resultData);
    }
    /**
     * Sets internal pointer to last element
     *
     * @return void
     */
    public function last(): void
    {
        $this->rowIndex = count($this->resultData) - 1;
    }
    /**
     * Returns the index of the given variable name of the array
     *
     * @param string $variableName
     * @return integer
     */
    public function rowIndex(string $variableName): int
    {
        foreach ($this->resultData as $rowIndex => $variableData) {
            if (strcasecmp($variableData['variable'], $variableName) === 0)
                return $rowIndex;
        }
        return -1;
    }
    /**
     * Returns the data of the given columnIndex of the array.
     * e.g. 
     *   [
     *      "index": 16,
     *      "gridConsumption": { "value": 0.4,"unit": "kWh" },
     *      "loads": { "value": 6.9,"unit": "kWh" },
     *      "feedin": { "value": 11.5,"unit": "kWh" },
     *      "input": { "value": 23.1,"unit": "kWh" },
     *      "generation": { "value": 18.2,"unit": "kWh" },
     *      "chargeEnergyToTal": { "value": 4.5,"unit": "kWh" },
     *      "dischargeEnergyToTal": { "value": 2.6,"unit": "kWh" }
     *   ]
     * 
     * @param integer $colIndex index to be retrieved or -1 to retrieve the last column
     * @return array
     */
    public function column(int $colIndex): array
    {
        $column = [];
        foreach ($this as $rowIndex => $variable) {
            if (!$variable->set($colIndex)) {
                if ($colIndex == -1)
                    return [];
                throw new Exception('illegal column index');
            }
            $value = $variable->current();
            if ($rowIndex === 0) {
                $column[$value->dataLabel()] = $value->headerValue();
            }
            $values['value'] = $value->value();
            $values['unit'] = $variable->unit();
            $column[$variable->varName()] = $values;
        }
        return $column;
    }
    public function getMinMax(array $filterVars = []): array
    {
        if (!$this->valid())
            return [];

        // Find todays minimum and maximum of variable related timestamp, as well
        // as the latest (current) Value and a trend -1=decreasing 0=constant 1=increasing
        $minMax = [];
        foreach ($this as $var) {
            if (!$var->valid())
                continue;
            if(count($filterVars) > 0 && !in_array($var->varName(),$filterVars))
                continue;
            $min = null;
            $max = null;
            $last = -1;
            foreach ($var as $key => $data) {
                $value = $data->value();
                if ($min === null || $value < $min->value()) {
                    $min = $data;
                }
                if ($max === null || $value >= $max->value()) {
                    $max = $data;
                }

                $trend = $value == $last ? 0 : ($value > $last ? 1 : -1);
                $last = $value;
            }
            // position to the very last (latest) entry
            $var->last();
            $current = $var->current()->value();

            // output values
            $minMax[$var->varName()] = [
                'unit' => $var->unit(),
                'min' => ['value' => $min->value(), 'time' => $min->headerValue()->format('Y-m-d H:i:s')],
                'max' => ['value' => $max->value(), 'time' => $max->headerValue()->format('Y-m-d H:i:s')],
                'current' => $current,
                'trend' => $trend
            ];
        }
        return $minMax;
    }
}
