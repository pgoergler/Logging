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
    protected $defaultVars = array();

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
            if ($name == 'root')
            {
                $logger = $this->makeLogger('root');
                $logger->addAppender('root', $this->getDefaultAppender());
                $logger->debug(new \Exception("No logger [$name] configured."));
                return $logger;
            }

            throw new \RuntimeException("No logger [$name] configured.");
        }

        return $this->loggers[$name];
    }

    public function has($name)
    {
        return isset($this->loggers[$name]) && !is_null($this->loggers[$name]);
    }

    public function set($variable, $value)
    {
        $this->defaultVars[$variable] = $value;
    }

    protected function getDefaultAppender()
    {
        return new \Logging\Appenders\EchoAppender('DefaultAppender', 'ALL');
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
            foreach ($this->defaultVars as $variable => $value)
            {
                $logger->set($variable, $value);
            }
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
     * @return \Logging\Logger
     * @throws \Exception
     */
    protected function configureLogger($name, array $configuration)
    {
        $logger = $this->makeLogger($name);

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

    /**
     *
     * @param type $name
     * @return \Psr\Log\LoggerInterface
     */
    protected function makeLogger($name = 'root')
    {
        $class = $this->loggerClass;
        return new $class($name);
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    public function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val)
        {
            $replace['{' . $key . '}'] = \dump_flat($val, 1);
        }

        $message = str_replace('\\{', '${__accolade__}', $message);
        $message = str_replace('\\}', '{__accolade__}$', $message);

        /**
         * replace first {} with {0}
         * replace second {} with {1}
         * etc...
         */
        $index = 0;
        $contextKeys = array_keys($context);
        $c = function($matches) use(&$index, &$contextKeys)
        {
            if(array_key_exists($index, $contextKeys))
            {
                return '{' . ($contextKeys[$index++]) . '}';
            }
            return $matches[0];
        };
        $message = preg_replace_callback('#\{\}#', $c, $message);

        $replace['${__accolade__}'] = '{';
        $replace['{__accolade__}$'] = '}';

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    public function prettydump($variable, $context)
    {
        if (is_null($variable))
        {
            $lines = array('null');
        } elseif ($variable instanceof \DateTime)
        {
            $lines = array("\\datetime('" . $variable->format('Y-m-d H:i:sP') . "')");
        } elseif ($variable instanceof \DateInterval)
        {
            $lines = array("\\dateinterval('" . $variable->format('P%yY%mM%dDT%hH%iI%sS') . "')");
        } elseif (is_numeric($variable))
        {
            $lines = array($variable);
        } elseif (is_string($variable))
        {
            $message = $this->interpolate($variable, $context);
            $lines = explode("\n", $message);
        } elseif (is_bool($variable))
        {
            $lines = array($variable ? 'true' : 'false');
        } elseif ($variable instanceof \Closure)
        {
            $lines = array('{closure}');
        } elseif (is_resource($variable))
        {
            $lines = array("$variable");
        } else if ($variable instanceof \Exception)
        {
            $traces = $variable->getTrace();
            $lines = array();
            $lines[] = "\\" . get_class($variable) . '() throwed in file ' . $variable->getFile() . ' on line ' . $variable->getLine();
            $lines[] = 'With message : ' . $variable->getMessage();

            if (method_exists($variable, 'toArray'))
            {
                $lines[] = "With values : ";
                $dumpr = explode("\n", \dump_r($variable->toArray()));
                foreach ($dumpr as $row)
                {
                    $lines[] = $row;
                }
            }

            if (count($traces))
            {

                $lines[] = 'Stack trace:';
                foreach ($traces as $i => $trace)
                {
                    if (isset($trace['file']))
                    {
                        $str = "#$i " . $trace['file'] . '(' . $trace['line'] . '): ';
                    } else
                    {
                        $str = "#$i :";
                    }

                    if (isset($trace['class']))
                    {
                        $str .= $trace['class'] . (isset($trace['type']) ? $trace['type'] : '::') . $trace['function'] . '(';
                    } else
                    {
                        $str .= $trace['function'] . '(';
                    }


                    $first = true;
                    foreach ($trace['args'] as $args)
                    {

                        $str .= ($first ? '' : ', ') . \dump_flat($args, 1);
                        $first = false;
                    }

                    $lines[] = $str . ')';
                }
            } else
            {
                $lines[] = $variable->getMessage();
            }
        } else
        {
            $lines = explode("\n", \dump_r($variable));
        }

        unset($variable);
        return $lines;
    }

}
