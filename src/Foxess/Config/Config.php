<?php

declare(strict_types=1);

namespace Foxess\Config;

use Foxess\Exceptions\Exception;

/**
 * Handles config variables for Foxess API
 */
abstract class Config
{
    protected const VARIABLES = ['username', 'hashed_password', 'device_id'];
    protected array $config = [];

    public function __construct()
    {
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
        foreach (self::VARIABLES as $variable) {
            if (!isset($this->config[$variable])) {
                throw new Exception("Error in configuration. Missing variable '" . $variable . "'");
            }
        }
    }
    /**
     * Get user name from configuration
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->config["username"];
    }
    /**
     * Get hashed password from configuration.
     * You can use the web site (e.g.) md5.cz to get the (md5) hashed password string
     *
     * @return string
     */
    public function getHashedPassword(): string
    {
        return $this->config["hashed_password"];
    }
    /**
     * Get device id from configuration.
     *
     * @return string
     */
    public function getDeviceId(): string
    {
        return $this->config["device_id"];
    }
}
