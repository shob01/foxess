<?php

declare(strict_types=1);

namespace Foxess;

use Foxess\DIContainer;

use \DateTime;
use DateTimeZone;
/**
 * Simple wrapper class for Foxess API report and raw data 
 */
class ResultData
{
    protected static DateTimeZone $tz;

    protected string $dataLabel;
    protected string $unit;
    /**
     * Undocumented function
     *
     * @param array $resultData   data array returned from either CloudApi class methods
     *                            getReport() or getRaw()
     */
    public function __construct(protected array $resultData)
    {
        if(!isset(self::$tz))
            self::$tz = DIContainer::getInstance()->get("TZ");
    }
    /**
     * Returns the $rowIndex entry from the array
     *
     * @param integer $rowIndex
     * @return array
     */
    public function row(int $rowIndex): array
    {
        $row = $this->resultData[$rowIndex];
        $this->unit = Constants::VARIABLES[$row["variable"]];
        return $row;
    }
    /**
     * Returns the $dataIndex data entry from row $rowIndex from the array
     *
     * @param integer $rowIndex
     * @param integer $dataIndex
     * @return array
     */
    public function rowData(int $rowIndex, int $dataIndex): array
    {
        $data = $this->row($rowIndex)['data'];
        if (empty($data))
            return [];

        if ($dataIndex < 0)
            $dataIndex = sizeof($data) - 1;

        return $data[$dataIndex];
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
            $this->dataLabel = array_key_first($this->rowData(0, 0));
        return $this->dataLabel;
    }
    /**
     * Returns the $dataIndex header entry from row $rowIndex from the array.
     * This is either the "index" or "time" value.
     *
     * @param integer $rowIndex
     * @param integer $dataIndex
     * @return mixed
     */
    public function rowDataHeader(int $rowIndex, int $dataIndex): mixed
    {
        $data = $this->rowData($rowIndex, $dataIndex);
        if (empty($data))
            return null;

        $headValue = $data[$this->dataLabel()];
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
     * Returns the $dataIndex value entry from row $rowIndex from the array.
     *
     * @param integer $rowIndex
     * @param integer $dataIndex
     * @return float|null
     */
    public function rowDataValue(int $rowIndex, int $dataIndex): float|null
    {
        $data = $this->rowData($rowIndex, $dataIndex);
        if (empty($data))
            return null;
        $value = $data['value'];
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
    public function column(int $colIndex) : array
    {
        foreach ($this->resultData as $rowIndex => $variableData) {
            if ($rowIndex === 0)
                $column[$this->dataLabel()] = $this->rowDataHeader($rowIndex, $colIndex);
            $value = $this->rowDataValue($rowIndex, $colIndex);
            $column[$variableData['variable']] = $value;
        }
        return $column;
    }
}
