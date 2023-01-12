<?php

namespace Kubinyete\Assertation\Util;

abstract class StringInterpolation
{
    public const DEFAULT_SYMBOL_START = '{';
    public const DEFAULT_SYMBOL_END = '}';
    public const DEFAULT_ESCAPE = '\\';
    public const DEFAULT_IDENTIFIER_SEPARATOR = ',';

    public static function interpolate(
        string $string,
        array $values,
        $default = '',
        string $symbol = self::DEFAULT_SYMBOL_START,
        string $symbolEnd = self::DEFAULT_SYMBOL_END,
        string $escape = self::DEFAULT_ESCAPE,
        string $identifierSeparator = self::DEFAULT_IDENTIFIER_SEPARATOR
    ): string {
        return preg_replace_callback(
            '/([^\\' . $escape . ']|^)(\\' . $symbol . '([a-zA-Z0-9\\' . implode('\\', [$identifierSeparator, $symbol, $symbolEnd]) . ']+)\\' . $symbolEnd . ')/',
            function (array $match) use ($values, $default, $identifierSeparator, $symbol, $symbolEnd, $escape) {
                $identifier = self::interpolate($match[3], $values, $default, $symbol, $symbolEnd, $escape, $identifierSeparator);
                $identifier = explode($identifierSeparator, $identifier);
                $args = count($identifier) > 1 ? array_slice($identifier, 1) : [];
                $identifier = $identifier[0];
                $value = ArrayUtil::get($identifier, $values, $default);

                if (is_callable($value)) {
                    $value = call_user_func_array($value, $args);
                }

                return $match[1] . $value;
            },
            $string
        );
    }
}
