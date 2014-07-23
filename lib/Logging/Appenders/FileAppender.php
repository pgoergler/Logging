<?php

namespace Logging\Appenders;

/**
 * Description of FileAppender
 *
 * @author paul
 */
class FileAppender extends EchoAppender
{

    protected $file;
    protected $append = true;

    protected function configure(array $configuration)
    {
        parent::configure($configuration);

        if (is_array($configuration))
        {
            $this->file = isset($configuration['filename']) ? $configuration['filename'] : 'php://stderr';
            $this->append = isset($configuration['append']) ? $configuration['append'] : true;
        } else
        {
            $this->file = $configuration;
        }

        $this->set('today', date('Ymd'));
    }

    protected function write($string)
    {
        $filename = strtr($this->file, $this->vars);

        if (preg_match('#^php://#', $filename))
        {
            $append = false;
        } else
        {
            $append = $this->append;
        }

        $file = ($append) ? fopen($filename, "a+") : fopen($filename, "w+");

        if ($file)
        {
            $waitForLock = true;
            if( flock($file, LOCK_EX, $waitForLock) )
            {
                fputs($file, $string);
                fflush($file);
                flock($file, LOCK_UN);
                fclose($file);
                return true;
            }
            else
            {
                fclose($file);
                parent::write("ERROR $filename lock failed\n");
                parent::write($string);
            }
        }

        parent::write("ERROR $filename not exists or you dont have permissions\n");
        parent::write($string);
        return false;
    }

}

?>
