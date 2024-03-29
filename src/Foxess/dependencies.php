<?php declare(strict_types=1);

use Foxess\DIContainer;
use Foxess\Config\Config;
use Foxess\Config\ConfigFile;
use Foxess\Config\ConfigDotEnv;
use Foxess\Requester\IRequester;
use Foxess\Requester\GuzzleHttpRequester;

$container = DIContainer::getInstance();

//$container->set(Config::class,fn() => new ConfigFile(__DIR__ . "/../../foxess_config.json"));
$container->set(Config::class,fn() => new ConfigDotEnv());
$container->set(IRequester::class,fn() => new GuzzleHttpRequester());
$container->set('TZ',fn() => new DateTimeZone("Europe/Berlin"));

