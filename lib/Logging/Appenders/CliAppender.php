<?php

namespace Logging\Appenders;

/**
 * Description of CliLogger
 *
 * @author paul
 */
class CliAppender extends EchoAppender
{

    protected $styles = array(
        'DEFAULT' => array(
            'bg' => 'black',
            'fg' => 'white',
            'bold' => false,
            'format' => "\033[%codes%m%prefix%\033[0m%message%\n",
        ),
        /* 'TRACE' => array(
          //'bg' => 'black',
          //'fg' => 'green',
          'bold' => false,
          ), */
        'debug' => array(
            //'bg' => 'black',
            'fg' => 'green',
            'bold' => true,
            'format' => "\033[%codes%m%prefix%\033[0m%message%\n",
        ),
        'info' => array(
            'fg' => 'cyan',
            'bold' => true,
            'format' => "\033[%codes%m%prefix%\033[0m%message%\n",
        ),
        'notice' => array(
            //'bg' => 'cyan',
            'fg' => 'magenta',
            'bold' => true,
            'format' => "\033[%codes%m%prefix%\033[0m%message%\n",
        ),
        'warning' => array(
            //'bg' => 'red',
            'fg' => 'yellow',
            'bold' => true,
            'format' => "\033[%codes%m%prefix%\033[0m%message%\n",
        ),
        'error' => array(
            'bg' => 'red',
            'fg' => 'white',
            'bold' => true,
            'format' => "\033[%codes%m%prefix%\033[0m%message%\n",
        ),
        'critical' => array(
            'bg' => 'red',
            'fg' => 'yellow',
            'blink' => false,
            'bold' => true,
            'format' => "\033[%codes%m%prefix%\033[0m%message%\n",
        ),
        'alert' => array(
            'bg' => 'red',
            'fg' => 'white',
            'blink' => true,
            'bold' => true,
            'format' => "\033[%codes%m%prefix%%message%\033[0m\n",
        ),
        'emergency' => array(
            'bg' => 'red',
            'fg' => 'yellow',
            'blink' => true,
            'bold' => true,
            'format' => "\033[%codes%m%prefix%%message%\033[0m\n",
        )
    );
    protected $options = array('bold' => 1, 'underscore' => 4, 'blink' => 5, 'reverse' => 7, 'conceal' => 8);
    protected $foreground = array('black' => 30, 'red' => 31, 'green' => 32, 'yellow' => 33, 'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'white' => 37);
    protected $background = array('black' => 40, 'red' => 41, 'green' => 42, 'yellow' => 43, 'blue' => 44, 'magenta' => 45, 'cyan' => 46, 'white' => 47);

    protected function getStyle($level)
    {
        return !in_array($level, array_keys($this->styles)) ? $this->styles['DEFAULT'] : $this->styles[$level];
    }

    protected function getCodes($style)
    {

        $codes = array();

        if (isset($style['fg']))
        {
            $codes[] = $this->foreground[$style['fg']];
        }

        if (isset($style['bg']))
        {
            $codes[] = $this->background[$style['bg']];
        }

        foreach ($this->options as $option => $value)
        {
            if (isset($style[$option]) && $style[$option])
            {
                $codes[] = $value;
            }
        }

        return $codes;
    }

    public function format($prefix, $level, $nLine, $text)
    {
        $style = $this->getStyle($level);
        $codes = $this->getCodes($style);
        if (empty($codes))
        {
            return $prefix . $text . "\n";
        }

        $replace = array(
            '%codes%' => implode(';', $codes),
            '%prefix%' => $prefix,
            '%message%' => $text,
        );
        return strtr($style['format'], $replace);
        //return "\033[" . implode(';', $codes) . 'm' . $prefix . "\033[0m" . $text . "\n";
    }

}

?>
