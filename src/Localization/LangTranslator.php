<?php

namespace Kubinyete\Assertation\Localization;

use UnexpectedValueException;

class LangTranslator extends BaseTranslator
{
    private const DEFAULT_LANG = 'en_US';
    private const PATH_LANG = 'lang';
    private const PATH_LANG_EXT = '.php';

    protected array $table;

    public function __construct(string $lang = self::DEFAULT_LANG)
    {
        $this->table = $this->includeLangFile($lang);
    }

    protected function includeLangFile(string $lang): array
    {
        $path = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', self::PATH_LANG, $lang . self::PATH_LANG_EXT]);

        if (file_exists($path)) {
            return include $path;
        }

        throw new UnexpectedValueException("Provided language $lang is not yet supported.");
    }

    protected function tr(string $message): ?string
    {
        return $this->table[$message] ?? null;
    }

    public function translate(string $message, array $context = []): string
    {
        $messageFromTable = $this->tr($message);

        if ($messageFromTable) {
            return parent::translate($messageFromTable, $context);
        }

        // @NOTE: 
        // Prevents any string interpolation for non-indified messages.
        return $message;
    }
}
