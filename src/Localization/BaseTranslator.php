<?php

namespace Kubinyete\Assertation\Localization;

use Kubinyete\Assertation\Util\StringInterpolation;

abstract class BaseTranslator implements TranslatorInterface
{
    public function translate(string $message, array $context = []): string
    {
        return StringInterpolation::interpolate($message, $context);
    }
}
