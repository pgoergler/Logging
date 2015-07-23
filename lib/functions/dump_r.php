<?php

function dump_key($key)
{
    if (is_numeric($key))
    {
        return $key;
    }
    return "'$key'";
}

function dump_r($variable, $padChar = "    ", $level = 0)
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

    if (is_array($variable) || $variable instanceof \Iterator || is_object($variable))
    {
        if (is_array($variable) || $variable instanceof \Iterator)
        {
            $class = "array";
            $openTag = "(";
            $closeTag = ")";
            $array = $variable;
        } else
        {
            $class = '\\' . get_class($variable);
            $openTag = " {";
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
        foreach ($array as $key => $value)
        {
            $hasProperties = true;
            $lines .= "\n" . str_repeat($padChar, $level + 1) . \dump_key($key) . " => " . \dump_r($value, $padChar, $level + 1) . ",";
        }
        return $lines . ( $hasProperties ? "\n" . str_repeat($padChar, $level) : '') . "$closeTag";
    } else
    {
        return \dump_r(array_map('trim', explode("\n", print_r($variable, true))), $padChar, $level + 1);
    }
}
