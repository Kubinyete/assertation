<?php

namespace Kubinyete\Assertation\Localization;

interface TranslatorInterface
{
    function translate(string $message, array $context = []): string;
}
