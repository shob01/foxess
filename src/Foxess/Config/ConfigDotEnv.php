<?php

declare(strict_types=1);

namespace Foxess\Config;

use Dotenv\Dotenv;

/**
 * Handles config file for Foxess API
 */
class ConfigDotEnv extends Config
{
    public function __construct(protected $configDir = null, protected $envFile = '.env')
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
        if ($this->configDir === null) {
            $this->configDir = $_SERVER['DOCUMENT_ROOT'];
        }
        $dotenv = Dotenv::createImmutable($this->configDir, $this->envFile);
        $dotenv->load();
        foreach (self::VARIABLES as $variable) {
            $env = $_ENV[strtoupper($variable)];
            if($env !== false)
                $this->config[$variable] = $env;
        }

        return $this->config;
    }
}
