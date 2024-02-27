<?php

declare(strict_types=1);

namespace Foxess\Config;

use Foxess\Exceptions\Exception;
use Foxess\Constants;

/**
 * Abstract class fort handling of config variables for Foxess API
 */
abstract class Config
{
    protected $requiredVariables = [];
    protected array $config = [];

    public function __construct()
    {
        $this->requiredVariables = CONSTANTS::CONFIG_VARIABLES;
        $this->getConfig();
        $this->checkConfig();
    }
    /**
     * Gets the configuration variables and stores them into the internal config array
     */
    abstract public function getConfig(): array;
    /**
     * Checks the completeness of the configuration. 
     * Throws an Exception if something is missing.
     *
     * @return void
     */
    protected function checkConfig()
    {
        foreach ($this->requiredVariables as $variable) {
            if (!isset($this->config[$variable])) {
                throw new Exception("Error in configuration. Missing variable '" . $variable . "'");
            }
        }
    }
    /**
     * Get an array with names of the required variables
     *
     * @return array
     */
    public function getRequiredVariableNames(): array
    {
        return $this->requiredVariables;
    }
    /**
     * Get variable value for name from configuration
     *
     * @return string or null if not existing
     */
    public function get($name): string|null
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }
}
