<?php

namespace Logging\Appenders;

/**
 * Description of NoAppender
 *
 * @author paul
 */
class NoAppender extends \Logging\Appender
{
    protected function write($var)
    {
        
    }

    public function format($prefix, $level, $nLine, $text)
    {
        return '';
    }

    public function parse($variable)
    {
        return '';
    }

    public function prefix($level)
    {
        return '';
    }
}

?>
