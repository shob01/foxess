<?php

declare(strict_types=1);

namespace Foxess\Formatter;

use Foxess\Constants;
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
     * @param string $unit      values unit (e.g. kW, %, A ... ) or empty string 
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
     * @param string $columnName        "index" or "time"
     * @param int|string $columnValue   if column name = "index" this is an integer value.
     *                                  if column name = "time" this is an date time string
     *                                  like "2023-08-11 12:02:57 CEST+0200".
     * @return string returns output data
     */
    public function headFieldValue(string $columnName, mixed $columnValue): string
    {
        if ($columnName === "time") {
            // given date format is like "2023-08-11 12:02:57 CEST+0200"
            // The part "CEST+0200" needs to be ignored to get a correct DateTime
            $date = DateTime::createFromFormat("Y-m-d H:i:s +", $columnValue);
            if ($date === false) {
                //Something went wrong ???
                $error = DateTime::getLastErrors();
                $valueStr = $columnValue;
            } else {
                $valueStr = $date->format("d.m.Y H:i:s");
            }
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
    abstract public function field(string $valueStr,string $type="text"): string;
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
     * @return string  eturns the given data table transformed into a string representation
     */
    public function transform(array $variableData, bool $addTotalColumn = false): string
    {
        $output = $this->begin();
        $rowNo = 0;
        foreach ($variableData as $variable) {
            if ($rowNo == 0) {
                // build head line
                $output .= $this->beginHeader();
                $output .= $this->beginLine();
                $output .= $this->headField('Variable');
                $output .= $this->fieldSeperator();
                $output .= $this->headField('Unit');
                if (!empty($variable["data"]))
                    $firstKey = array_key_first($variable["data"][0]);
                foreach ($variable["data"] as $varData) {
                    $output .= $this->fieldSeperator();
                    $output .= $this->headField($this->headFieldValue($firstKey, $varData[$firstKey]));
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
            $output .= $this->field($variable["variable"]);
            // determine the variables unit from the Constants table
            $unit = Constants::VARIABLES[$variable["variable"]];
            $output .= $this->fieldSeperator();
            $output .= $this->field($unit);

            $totalValue = 0;
            foreach ($variable["data"] as $varData) {
                $output .= $this->fieldSeperator();
                $value = $varData["value"];
                if ($addTotalColumn)
                    $totalValue += $value;
                $output .= $this->field($this->fieldValue($value, $unit),"number");
            }
            if ($addTotalColumn) {
                $output .= $this->fieldSeperator();
                $output .= $this->field($this->fieldValue($totalValue, $unit),"number");
            }
            $output .= $this->endLine();

            $rowNo++;
        }
        $output .= $this->end();
        return $output;
    }
}
