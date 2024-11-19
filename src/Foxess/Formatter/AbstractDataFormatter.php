<?php

declare(strict_types=1);

namespace Foxess\Formatter;

use Foxess\Constants;
use Foxess\ResultData\ResultDataTable;

use \DateTime;

/**
 * Abstract implementation of the IDataFormatter interface. 
 * This class is used to implement basic functionality that is relevant for
 * different Formatter implementations
 */
abstract class AbstractDataFormatter implements IDataFormatter
{
    /**
     * Constructor
     * 
     * @param string $decimalSep    character used as decimal seperator
     * @param string $thousandsSep  character used as thousands seperator
     */
    public function __construct(
        protected $decimalSep = ",",
        protected $thousandsSep = "."
    ) {
    }
    /**
     * Formats a numeric value into a string, depending on the unit of the value
     *
     * @param float|int $value  value to be transformed
     * @param string $unit      values unit (e.g. kWh, %, A ... ) or empty string 
     *                          if no unit is available
     * @return string           string representation of the value
     */
    public function numToStr(mixed $value, string $unit): string
    {
        switch ($unit) {
            case 'kW':
                $valueStr = number_format($value, 3, $this->decimalSep, $this->thousandsSep);
                break;
            case '%':
            case '':
                $valueStr = number_format($value, 0, $this->decimalSep, $this->thousandsSep);
                break;
            default:
                $valueStr = number_format($value, 1, $this->decimalSep, $this->thousandsSep);
        }
        return $valueStr;
    }
    /**
     * This method will be called by the transform() method
     * before the data is processed.
     * This method needs to be implemented by the deriving class
     * 
     * @return string
     */
    abstract public function begin(): string;
    /**
     * This method will be called by the transform() method
     * to indicate the begin of the header area
     *
     * @return string returns output data
     */
    abstract public function beginHeader(): string;
    /**
     * This method will be called by the transform() method
     * to indicate the end of the header area
     *
     * @return string returns output data
     */
    abstract public function endHeader(): string;

    /**
     * This method will be called by the transform() method
     * to output the value of a header field.
     * This method needs to be implemented by the deriving class
     * 
     * @param string $value     value of the header field
     * @return string returns output data
     */
    abstract public function headField(string $value): string;
    /**
     * This method will be called by the transform() method
     * to format the column value of a header field.
     * This method can be overwritten in case the default implementaion does 
     * not meet your requirements.
     * 
     * @param int|string $columnValue   DateTime or integer
     * @return string returns output data
     */
    public function headFieldValue(mixed $columnValue): string
    {
        if ($columnValue instanceof DateTime) {
            $valueStr = $columnValue->format("d.m.Y H:i:s");
        } else {
            $valueStr = (string)$columnValue;
        }
        return $valueStr;
    }
    /**
     * This method will be called by the transform() method
     * to indicate the begin of a new data line
     *
     * @return string returns output data
     */
    abstract public function beginLine(): string;
    /**
     * This method will be called by the transform() method
     * to indicate the end of a data line
     *
     * @return string returns output data
     */
    abstract public function endLine(): string;
    /**
     * This method will be called by the transform() method
     * to output the value of a data field.
     * This method needs to be implemented by the deriving class
     * 
     * @param string $valueStr  value string of the data field
     * @param string $type      "number"|"date"|"text"
     * @return string returns output data
     */
    abstract public function field(string $valueStr, string $type = "text"): string;
    /**
     * This method will be called by the transform() method
     * to format the value of a data field.
     * This method can be overwritten in case the default implementaion does 
     * not meet your requirements.
     *
     * @param float $value      value of a data field
     * @param string $unit      values unit (e.g. kW, %, A ... ) or empty string 
     *                          if no unit is available
     * @return string returns output data
     */
    public function fieldValue(float $value, string $unit): string
    {
        return $this->numToStr($value, $unit);
    }
    /**
     * This method will be called by the transform() method
     * to output a field seperator string. If a a field seperator is not 
     * applicable, just return an empty string
     *
     * @return string returns output data
     */
    abstract public function fieldSeperator(): string;
    /**
     * This method will be called by the transform() method
     * after all data has been processed.
     * This method needs to be implemented by the deriving class
     * 
     * @return string returns output data
     */
    abstract public function end(): string;
    /**
     * Undocumented function
     *
     * @param array $variableData
     * @param boolean $addTotalColumn
     * @return string returns output data
     */
    /**
     * Transforms the given data into an output string
     *
     * @param array $variableData   data array returned from either CloudApi class methods
     *                              getReport() or getRaw()
     * @param boolean $addTotalColumn if true, a total colum will be added at the end of each
     *                               data line
     * @return string  returns the given data table transformed into a string representation
     */
    public function transform(array $resultData, bool $addTotalColumn = false): string
    {
        $resultDataTable = new ResultDataTable($resultData);
        $output = $this->begin();
        foreach ($resultDataTable as $rowIndex => $variable) {
            if ($rowIndex == 0) {
                // build head line
                $output .= $this->beginHeader();
                $output .= $this->beginLine();
                $output .= $this->headField('Variable');
                $output .= $this->fieldSeperator();
                $output .= $this->headField('Unit');
                foreach ($variable as $varData) {
                    $output .= $this->fieldSeperator();
                    $output .= $this->headField($this->headFieldValue($varData->headerValue()));
                }
                if ($addTotalColumn) {
                    $output .= $this->fieldSeperator();
                    $output .= $this->headField('Total');
                }
                $output .= $this->endLine();
                $output .= $this->endHeader();
            }
            $output .= $this->beginLine();
            // output the name of the variable
            $output .= $this->field($variable->varName());
            // determine the variables unit from the Constants table
            $unit = $variable->unit();
            $output .= $this->fieldSeperator();
            $output .= $this->field($unit);

            $totalValue = 0;
            foreach ($variable as $varData) {
                $output .= $this->fieldSeperator();
                $value = $varData->value();
                if ($addTotalColumn)
                    $totalValue += $value;
                $output .= $this->field($this->fieldValue($value, $unit), "number");
            }
            if ($addTotalColumn) {
                $output .= $this->fieldSeperator();
                $output .= $this->field($this->fieldValue($totalValue, $unit), "number");
            }
            $output .= $this->endLine();
        }
        $output .= $this->end();
        return $output;
    }
}
