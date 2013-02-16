<?php

namespace Logging\Appenders;

/**
 * Description of EchoAppender
 *
 * @author paul
 */
class EchoAppender extends DefaultAppender
{
    protected function write($message)
    {
        echo $message;
    }
}

?>
