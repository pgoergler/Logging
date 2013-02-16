<?php

namespace Logging;

/**
 * Description of Appender
 *
 * @author paul
 */
abstract class Appender
{

    protected $name = null;
    protected $vars = array();
    protected $levels = array();
    protected $prefix = '';
    public static $defaultPrefix = '[%datetime%][%level%][%file%][%function%@%line%]';

    public function __construct($name, $levels, $prefix, array $configuration = array())
    {
        $this->setName($name);
        $this->setLevels($levels);
        $this->prefix = is_null($prefix) ? static::$defaultPrefix : $prefix;

        $this->configure($configuration);
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

    public function setLevels($levels)
    {
        if (is_array($levels))
        {
            $this->levels = $levels;
        } elseif( $levels == '*' || $levels == 'ALL' )
        {
            $this->levels = 'ALL';
        }else
        {
            foreach (preg_split('#,#', $levels) as $level)
            {
                $this->levels[] = constant('\Psr\Log\LogLevel::' . $level);
            }
        }
    }

    public function getLevels()
    {
        return $this->levels;
    }

    public function get($varname, $defaultValue)
    {
        return isset($this->vars['%' . $varname . '%']) ? $this->vars['%' . $varname . '%'] : $defaultValue;
    }

    public function set($varname, $value)
    {
        $this->vars['%' . $varname . '%'] = $value;
    }

    protected function configure(array $configuration)
    {
        $this->set('dateFormat', 'Y-m-d H:i:s');

        $this->makeDefaultsVars();
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val)
        {
            $replace['{' . $key . '}'] = $val;
        }

        $message = str_replace('\\{', '${__accolade__}', $message);
        $message = str_replace('\\}', '{__accolade__}$', $message);
        $replace['${__accolade__}'] = '{';
        $replace['{__accolade__}$'] = '}';

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    protected function makeDefaultsVars()
    {
        if (isset($_SERVER['SSH_CLIENT']))
        {
            $arr = explode(' ', $_SERVER['SSH_CLIENT']);
            $this->set('client_ip', (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'unknown') . '@' . $arr[0] . ':' . $arr[2]);
            $this->set('client_useragent', $_SERVER['TERM']);
        } else if (isset($_SERVER['TERM']))
        {
            $this->set('client_ip', (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'unknown') . '@localhost');
            $this->set('client_useragent', $_SERVER['TERM']);
        } else
        {
            $this->set('client_ip', isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNSET_IP'));
            $this->set('client_useragent', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'UNSET_UA');
        }
    }

    public function log($level, $message, array $context, array $stackTrace)
    {
        if ('ALL' != $this->levels && !in_array($level, $this->levels))
        {
            return;
        }

        $this->set('pid', (string) getmypid());
        $this->set('file', basename($stackTrace[0]['file']));
        $this->set('function', isset($stackTrace[1]) ? $stackTrace[1]['function'] : '');
        $this->set('line', $stackTrace[0]['line']);

        $lines = $this->parse($this->interpolate($message, $context));
        $prefix = $this->prefix($level);
        $toLog = '';

        foreach ($lines as $n => $strResult)
        {
            $toLog .= $this->format($prefix, $level, $n, $strResult);
        }

        $this->write($toLog);
    }

    public abstract function format($prefix, $level, $nLine, $text);

    public abstract function parse($variable);

    public abstract function prefix($level);

    protected abstract function write($message);
}

?>