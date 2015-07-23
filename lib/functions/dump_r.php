<?php

function dump_key($key)
{
    if (is_numeric($key))
    {
        return $key;
    }
    return "'$key'";
}

function dump_simple_var($variable, $qualifyAll = true)
{
    if (is_null($variable))
    {
        return "null";
    }

    if (is_bool($variable))
    {
        return ($variable ? 'true' : 'false');
    }

    if (is_string($variable))
    {
        return "'$variable'";
    }

    if (is_numeric($variable))
    {
        return "$variable";
    }
    if ($qualifyAll)
    {
        if (is_object($variable))
        {
            return "\\" . get_class($variable) . "{...}";
        }
        if (is_array($variable))
        {
            return "array(...)";
        }

        return gettype($variable) . "{...}";
    }
    return null;
}

function is_simple_var($variable)
{
    if (is_null($variable))
    {
        return true;
    }

    if (is_bool($variable))
    {
        return true;
    }

    if (is_string($variable))
    {
        return true;
    }

    if (is_numeric($variable))
    {
        return true;
    }
    return false;
}

function dump_r($variable, $padChar = "    ", $depth = 0, $maxDepth = null)
{
    if (is_simple_var($variable))
    {
        return \dump_simple_var($variable);
    }

    if (is_array($variable) || $variable instanceof \Iterator || is_object($variable))
    {
        if (is_array($variable) || $variable instanceof \Iterator)
        {
            $class = "array";
            $openTag = "(";
            $joinTag = " => ";
            $closeTag = ")";
            $array = $variable;
        } else
        {
            $class = '\\' . get_class($variable);
            $openTag = "{";
            $joinTag = " = ";
            $closeTag = "}";

            if (method_exists($variable, 'toArray'))
            {
                $array = $variable->toArray();
            } else
            {
                $array = get_object_vars($variable);
            }
        }

        $lines = "{$class}{$openTag}";
        $hasProperties = false;
        $first = true;
        foreach ($array as $key => $value)
        {
            $hasProperties = true;
            $lines .= ($first ? "" : "," ) . "\n" . str_repeat($padChar, $depth + 1) . \dump_key($key) . $joinTag . ( is_null($maxDepth) || $depth < $maxDepth ? \dump_r($value, $padChar, $depth + 1, $maxDepth) : \dump_simple_var($value, true));
            $first = false;
        }
        return $lines . ( $hasProperties ? "\n" . str_repeat($padChar, $depth) : '') . "$closeTag";
    } else
    {
        return \dump_r(array_map('trim', explode("\n", print_r($variable, true))), $padChar, $depth + 1, $maxDepth);
    }
}

function dump_flat($variable, $maxDepth, $padChar = "    ")
{
    $rows = array_map('trim', explode("\n", \dump_r($variable, $padChar, 0, $maxDepth)));
    $flat = "";

    $first = true;
    foreach ($rows as $row)
    {
        if (!$first)
        {
            if (!in_array($row, ['{', '}', '(', ')', '[', ']']))
            {
                $flat .= " ";
            }
        }
        $flat .= $row;
        $first = false;
    }
    return $flat;
}
