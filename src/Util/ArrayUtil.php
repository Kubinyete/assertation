<?php

namespace Kubinyete\Assertation\Util;

abstract class ArrayUtil
{
    public const SEPARATOR = '.';

    public static function get(string $path, array $array, mixed $default = null): mixed
    {
        $splitPath = explode(self::SEPARATOR, $path);

        while (is_array($array) && ($key = array_shift($splitPath))) {
            if (array_key_exists($key, $array)) {
                $array = &$array[$key];
            } else {
                $array = null;
            }
        }

        return is_null($array) || !empty($splitPath) ? $default : $array;
    }

    public static function set(string $path, array &$array, mixed $value = null): void
    {
        $splitPath = explode(self::SEPARATOR, $path);

        while (is_array($array) && ($key = array_shift($splitPath))) {
            if (!$splitPath) {
                $array[$key] = $value;
            } else {
                if (array_key_exists($key, $array) && is_array($array[$key])) {
                    $array = &$array[$key];
                } else {
                    $array[$key] = [];
                    $array = &$array[$key];
                }
            }
        }
    }
}
