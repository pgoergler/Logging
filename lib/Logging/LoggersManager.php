<?php

namespace Logging;

/**
 * Description of LoggersManager
 *
 * @author paul
 */
class LoggersManager
{

    protected static $loggers = array();
    protected static $appenders = array();

    public static function add(Logger $logger = null, $name = null)
    {
        $name = $name ? : $logger->getName();
        static::$loggers[$name] = $logger;
    }

    /**
     *
     * @return \Logging\Logger
     */
    public static function get($name = 'root')
    {
        if (!isset(static::$loggers[$name]) || is_null(static::$loggers[$name]))
        {
            throw new \RuntimeException("No logger [$name] configured.");
        }

        return static::$loggers[$name];
    }

    public static function configure(array $configuration)
    {
        foreach ($configuration['appenders'] as $name => $config)
        {
            static::configureAppenders($name, $config);
        }

        foreach ($configuration['loggers'] as $name => $config)
        {
            $logger = static::configureLogger($name, $config);
            static::$loggers[$name] = $logger;
        }

        if (!isset(static::$loggers['root']))
        {
            throw new \Exception('Logger [root] must be configured');
        }
    }

    protected static function configureLogger($name, array $configuration)
    {
        $logger = new Logger($name);

        foreach ($configuration['appenders'] as $name)
        {
            if (!isset(static::$appenders[$name]))
            {
                throw new \Exception('Appender ' . $name . ' not found.');
            }
            $logger->addAppender($name, static::$appenders[$name]);
        }
        return $logger;
    }

    protected static function configureAppenders($name, array $configuration)
    {
        extract($configuration);
        static::$appenders[$name] = new $class($name, $levels, $prefix, isset($param) ? $param : array());
    }

}

?>
