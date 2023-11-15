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
     *      "gridConsumption": 0.4,
     *      "loads": 6.9,
     *      "feedin": 11.5,
     *      "input": 23.1,
     *      "generation": 18.2,
     *      "chargeEnergyToTal": 4.5,
     *      "dischargeEnergyToTal": 2.6
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
            $val = $value->value();
            $column[$variable->name()] = $val;
        }
        return $column;
    }
}
