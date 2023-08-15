<?php declare(strict_types=1);

use Foxess\DIContainer;
use Foxess\Config;
use Foxess\Requester\IRequester;
use Foxess\Requester\GuzzleHttpRequester;
use Foxess\TokenStore\ITokenStore;
use Foxess\TokenStore\SessionTokenStore;

$container = DIContainer::getInstance();

$container->set(Config::class,fn() => new Config(__DIR__ . "/../../foxess_config.json"));
$container->set(IRequester::class,fn() => new GuzzleHttpRequester());
$container->set(ITokenStore::class,fn() => new SessionTokenStore());
$container->set('TZ',fn() => new DateTimeZone("Europe/Berlin"));

