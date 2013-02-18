<?php

namespace Logging\Appenders;

/**
 * Description of FileAppender
 *
 * @author paul
 */
class FileAppender extends DefaultAppender
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
            fputs($file, $string);
            fclose($file);
            return true;
        }

        echo "ERROR $filename not exists or you dont have permissions\n";
        echo $string;
        return false;
    }

}

?>
