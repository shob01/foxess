<?php declare(strict_types=1);

namespace Foxess;

use Foxess\Exceptions\Exception;

/**
 * Simplest possible DI Container
 *
 * Normally you will define your dependencies for example like this:
 * 
 * $container = DIContainer::getInstance();
 * $container->set('user','user@email.com');
 * $container->set('TZ',fn() => new DateTimeZone("Europe/Berlin"));
 * $container->set(Config::class,fn() => new Config("config.json"));
 *
 * And you will access the container for example like this
 * 
 * $user = DIContainer::getInstance()->get('user');
 * $tz = DIContainer::getInstance()->get("TZ");
 * $config = DIContainer::getInstance()->get(Config::class);

 */
class DIContainer 
{
    private array $container = [];

    private static $singleton;
    /**
     * Set a dependecy value.
     * This can be a simple value or a Closure. If the value is a instance of a class
     * you should always define it as a Closure like:
     * 
     * $container->set('TZ',fn() => new DateTimeZone("Europe/Berlin"));
     * 
     * This ensures that the instance will be created when it is really requested and only then.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value) :void
    {
        $this->container[$key] = $value;
    }
    /**
     * Gets a dependecy value
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key) : mixed
    {
        if (!$this->has($key)) {
            throw new Exception(sprintf("DI Container missing key '%s'!",$key));
        }

        if (is_callable($this->container[$key])) {
            $this->container[$key] = $this->execute($key);
        }

        return $this->container[$key];
    }
    /**
     * Checks the existance of a key 
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key) : bool
    {
        return isset($this->container[$key]);
    }
    /**
     * Executes the assigned function or Closure
     *
     * @param string $key
     * @return void
     */
    protected function execute(string $key)
    {
        if (!$this->has($key)) {
            throw new Exception(sprintf("DI Container missing key '%s'!",$key));
        }

        if (!is_callable($this->container[$key])) {
            throw new Exception(sprintf("DI Container key '%s' is not callable!",$key));
        }

        return call_user_func($this->container[$key]);
    }
    /**
     * Returns a singleton instance of this class
     *
     * @return DIContainer
     */
    public static function getInstance() : self
    {
        if(is_null(self::$singleton))
            self::$singleton = new self();
        return self::$singleton;
    }
}