<?php

declare(strict_types=1);

namespace Foxess\ResultData;

use Foxess\Constants;

use \Iterator;

/**
 * Variable entry of a ResultDataTable
 */
class Variable implements Iterator
{
    protected int $rowIndex = 0;
    protected string $name;
    protected string $unit;
    /**
     * Constructor
     *
     * @param array $variableData Reference to a ResultDataTable entry, which refers to
     *                            the variable data
     */
    public function __construct(protected array $variableData)
    {
        $this->name = $variableData['variable'];
        $this->unit = Constants::VARIABLES[$this->name];
    }
    /**
     * Returns the name of the variable
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    /**
     * Returns the unit of the values of the variable data
     *
     * @return string
     */
    public function unit(): string
    {
        return $this->unit;
    }
    /**
     * Iterator implementation
     *
     * @return Value
     */
    public function current(): Value | null
    {
        if (!$this->valid())
            return null;

        return new Value($this->variableData['data'][$this->key()], $this->unit);
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
        return $this->rowIndex >= 0 && $this->rowIndex < count($this->variableData['data']);
    }
    /**
     * Sets the internal pointer to given index. 
     * If index is < 0 internal pointer is set to the last element
     *
     * @param integer $index Index to be set
     * @return boolean
     */
    public function set(int $index): bool
    {
        if ($index < 0)
            $index = count($this->variableData['data']) - 1;

        $this->rowIndex = $index;
        return $this->valid();
    }
    /**
     * Sets internal pointer to last element
     *
     * @return void
     */
    public function last(): void
    {
        $this->rowIndex = count($this->variableData['data']) - 1;
    }
}
