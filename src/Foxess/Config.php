<?php declare(strict_types=1);

namespace Foxess;

use Foxess\Exceptions\Exception;

/**
 * Handles config file for Foxess API
 */
class Config
{
    protected $config = null;

    public function __construct(protected $configFile)
    {
        $this->getConfig();
        session_start();
    }
    /**
     * Reads the configuratiuon parameters from the config json file
     */
    public function getConfig(): array
    {
        if ($this->config !== null) {
            return $this->config;
        }
        $config = json_decode(file_get_contents($this->configFile), true);
        if (isset($config["username"]) && isset($config["hashed_password"]) && isset($config["device_id"])) {
            return $config;
        }
        throw new Exception("Error in configuration file '" . $this->configFile . "'");
    }
    /**
     * Get user name form config json file
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->getConfig()["username"];
    }
    /**
     * Get hashed password from config json file.
     * You can use the web site (e.g.) md5.cz to get the (md5) hashed password string
     *
     * @return string
     */
    public function getHashedPassword(): string
    {
        return $this->getConfig()["hashed_password"];
    }
    /**
     * Get user name form config json file
     *
     * @return string
     */
    public function getDeviceId(): string
    {
        return $this->getConfig()["device_id"];
    }
}
