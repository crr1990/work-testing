<?php

class MyConfig implements ArrayAccess
{
    protected $config = [];
    protected $env = 'production';

    protected static $instance;

    /**
     * Constructor.
     *
     * @param string $env
     * @internal param array $config
     */
    function __construct($env = null)
    {
        if (!$env) {
            $DADA_ENV = getenv("ENV");
            $this->env = $DADA_ENV ? $DADA_ENV : 'production';
        }
    }

    /**
     * Load dir
     *
     * @param $dir
     */
    static function load($dir)
    {
        return self::instance()->loadConfig($dir);
    }

    /**
     * Get config item
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    static function get($key, $default = null)
    {
        return self::instance()->getItem($key, $default);
    }

    /**
     * Has config item
     *
     * @param $key
     * @return bool
     */
    static function has($key)
    {
        return self::instance()->hasItem($key);
    }

    /**
     * Get all item
     *
     * @return array
     */
    static function all()
    {
        return self::instance()->getConfig();
    }

    /**
     * Get config group
     *
     * @param $prefix
     * @return array
     */
    static function group($prefix)
    {
        return self::instance()->getGroup($prefix);
    }

    /**
     * Set config item
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    static function set($key, $value)
    {
        return self::instance()->setItem($key, $value);
    }

    /**
     * Has config item
     *
     * @param $key
     */
    static function del($key)
    {
        return self::instance()->delItem($key);
    }

    /**
     * Get config item
     *
     * @param $key
     * @param $default
     */
    public function getItem($key, $default = null)
    {
        if (isset($this->config[$key])) return $this->config[$key];
        return $default;
    }

    /**
     * Get config group
     *
     * @param $prefix
     * @return array
     */
    public function getGroup($prefix)
    {
        $config = [];
        $len = strlen($prefix) + 1;
        foreach ($this->config as $k => $v) {
            if (strpos($k, $prefix . '.') === 0) {
                $config[substr($k, $len)] = $v;
            }
        }
        return $config;
    }

    /**
     * Get config item
     *
     * @param $key
     * @return bool
     */
    public function hasItem($key)
    {
        return isset($this->config[$key]);
    }

    /**
     *
     * Set config item
     *
     * @param $key
     * @param $value
     */
    public function setItem($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     *
     * Delete config item
     *
     * @param $key
     */
    public function delItem($key)
    {
        unset($this->config[$key]);
    }

    /**
     * Get all config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *
     * Load config dir
     *
     * @param $dir
     */
    public function loadConfig($dir)
    {
        $configPath = realpath($dir);

        // Path not found
        if (!$configPath) return $this;

        $defaultFile = $configPath . '/default.php';
        $envFile = $configPath . '/' . self::env() . '.php';
        $localFile = $configPath . '/local.php';

        $config = [];

        if (file_exists($defaultFile)) {
            $config = include($defaultFile);
        }

        if (file_exists($envFile)) {
            $config = array_merge($config, include($envFile));
        }

        if (file_exists($localFile)) {
            $config = array_merge($config, include($localFile));
        }

        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Get instance
     *
     * @return MyConfig
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
            self::$instance->loadConfig(dirname(__DIR__) . '/config');
        }

        return self::$instance;
    }

    /**
     * Get current env
     *
     * @return string
     */
    public static function env()
    {
        return self::instance()->getEnv();
    }

    /**
     * Get env
     *
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->hasItem($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->getItem($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->setItem($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->delItem($offset);
    }
}
