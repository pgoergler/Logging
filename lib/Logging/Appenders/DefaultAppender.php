<?php

namespace Logging\Appenders;

/**
 * Description of DefaultAppender
 *
 * @author paul
 */
abstract class DefaultAppender extends \Logging\Appender
{

    public function format($prefix, $level, $nLine, $text)
    {
        $lastChar = substr($text, -1);

        $ender = in_array($lastChar, array("\r", "\n")) ? "" : "\n";

        return $prefix . $text . $ender;
    }

    public function parse($variable, $context)
    {
        return \Logging\LoggersManager::getInstance()->prettydump($variable, $context);
    }

    public function prefix($level)
    {
        list($usec, $sec) = explode(" ", microtime());
        $this->set('datetime', date($this->get('dateFormat', 'Y-m-d H:i:s')) . ',' . sprintf("%03d", floor($usec * 1000)));
        $this->set('level', sprintf("% 9s", strtoupper($level)));

        $prefix = str_replace('%%', '${percent}', $this->prefix);
        $prefix = strtr($prefix, $this->vars);
        $prefix = str_replace('${percent}', '%', $prefix);
        return $prefix;
    }

}

?>
