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
        $obLevel = ob_get_level();
        $contents = array();

        for( $i = 0; $i < $obLevel; $i++ )
        {
            $contents[] = ob_get_clean();
        }

        echo $message;

        for( $i = $obLevel; $i > 0; $i-- )
        {
            ob_start();
            echo $contents[$i- 1];
        }

    }
}

?>
