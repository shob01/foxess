<?php declare(strict_types=1);

namespace Foxess\Config;

/**
 * Handles config file for Foxess API
 */
class ConfigFile extends Config
{
    public function __construct(protected $configFile)
    {
        parent::__construct();
    }
    /**
     * Reads the configuratiuon parameters from the config json file
     */
    public function getConfig(): array
    {
        if (!empty($this->config)) {
            return $this->config;
        }
        $this->config = json_decode(file_get_contents($this->configFile), true);

        return $this->config;
    }
}
