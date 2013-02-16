<?php

namespace Logging;

/**
 * Description of Logger
 *
 * @author paul
 */
class Logger extends \Psr\Log\AbstractLogger
{
    protected $name;
    protected $appenders = array();
    
    public function __construct($name, array $appenders = array())
    {
        $this->name = $name;
        $this->appenders = $appenders;
    }
    
    public function setName($name)
    {
        $this->set('name', $name);
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }    
    
    public function addAppender($name, $appender)
    {
        $this->appenders[$name] = $appender;
    }
    
    public function set($varname, $value)
    {
        foreach( $this->appenders as $appender )
        {
            $appender->set($varname, $value);
        }
    }    
    
    public function log($level, $message, array $context = array())
    {
        $stackTrace = debug_backtrace();
        array_shift($stackTrace);
        $this->set('file', basename($stackTrace[0]['file']));
        $this->set('function', isset($stackTrace[1]) ? $stackTrace[1]['function'] : '');
        $this->set('line', $stackTrace[0]['line']);
        
        foreach( $this->appenders as $name => $appender)
        {
            $appender->log($level, $message, $context, $stackTrace);
        }
    }
}

?>
