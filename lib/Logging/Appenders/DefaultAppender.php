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

    public function parse($variable)
    {
        if (is_null($variable))
        {
            $lines = array('null');
        } else if (is_bool($variable))
        {
            $lines = array($variable ? 'true' : 'false');
        } else if ($variable instanceof \Exception)
        {
            $lines = array();
            $traces = $variable->getTrace();
            $lines[] = 'Exception ' . get_class($variable) . ' throwed in file ' . $variable->getFile() . ' on line ' . $variable->getLine();
            $lines[] = 'With message : ' . $variable->getMessage();

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

                        $str .= ($first ? '' : ', ') . $this->flattern($args, 1);
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
            $lines = explode("\n", print_r($variable, true));
        }
        unset($variable);
        return $lines;
    }

    public function flattern($item, $level = 0)
    {
        if (is_string($item))
        {
            return "'$item'";
        } elseif (is_bool($item))
        {
            return $item ? 'true' : 'false';
        } elseif ($item instanceof \Closure)
        {
            return '{closure}';
        } elseif (is_object($item))
        {
            return get_class($item);
        } elseif (is_array($item))
        {
            if ($level > 0)
            {
                $self = &$this;

                $values = array();
                $iterator = 0;
                array_walk($item, function($value, $key) use(&$values, &$self, $level, &$iterator)
                        {
                            $sK = '';
                            if (!is_numeric($key) || $key != $iterator++)
                            {
                                $sK = is_numeric($key) ? $key : "'$key'";
                                $sK .= ' => ';
                            }
                            $values[] = $sK . $self->flattern($value, $level - 1);
                        });

                return 'array(' . implode(', ', $values) . ')';
            } else
            {
                return 'array';
            }
        }
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
