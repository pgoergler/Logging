<?php

namespace Logging;

/**
 * Description of LoggersManager
 *
 * @author paul
 */
class LoggersManager
{

    protected $loggers = array();
    protected $appenders = array();
    protected static $_instance = null;
    protected $loggerClass = '\Logging\Logger';

    /**
     *
     * @return LoggersManager
     */
    public static function getInstance()
    {
        if (is_null(static::$_instance))
        {
            static::$_instance = new LoggersManager();
        }
        return static::$_instance;
    }

    protected function __construct()
    {

    }

    public function add(Logger $logger = null, $name = null)
    {
        $name = $name ? : $logger->getName();
        $this->loggers[$name] = $logger;
    }

    /**
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function get($name = 'root')
    {
        if (!isset($this->loggers[$name]) || is_null($this->loggers[$name]))
        {
            throw new \RuntimeException("No logger [$name] configured.");
        }

        return $this->loggers[$name];
    }

    public function setLoggerClass($classname)
    {
        $this->loggerClass = $classname;
    }

    public function configure(array $configuration)
    {
        foreach ($configuration['appenders'] as $name => $config)
        {
            $this->configureAppenders($name, $config);
        }

        foreach ($configuration['loggers'] as $name => $config)
        {
            $logger = $this->configureLogger($name, $config);
            $this->loggers[$name] = $logger;
        }

        if (!isset($this->loggers['root']))
        {
            throw new \Exception('Logger [root] must be configured');
        }
    }

    /**
     *
     * @param array $name
     * @param array $configuration
     * @return \Psr\Log\LoggerInterface
     * @throws \Exception
     */
    protected function configureLogger($name, array $configuration)
    {
        $class = $this->loggerClass;
        $logger = new $class($name);

        foreach ($configuration['appenders'] as $name)
        {
            if (!isset($this->appenders[$name]))
            {
                throw new \Exception('Appender ' . $name . ' not found.');
            }
            $logger->addAppender($name, $this->appenders[$name]);
        }
        return $logger;
    }

    protected function configureAppenders($name, array $configuration)
    {
        extract($configuration);
        $this->appenders[$name] = new $class($name, $levels, $prefix, isset($param) ? $param : array());
    }

}

?>
