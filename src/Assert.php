<?php

namespace Kubinyete\Assertation;

use Kubinyete\Assertation\Localization\LangTranslator;
use Kubinyete\Assertation\Localization\NullTranslator;
use Kubinyete\Assertation\Localization\TranslatorInterface;

class Assert
{
    protected TranslatorInterface $tr;
    protected bool $throwEarly;

    protected function __construct()
    {
        $this->tr = new LangTranslator();
        $this->throwEarly = false;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->tr = $translator;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->tr;
    }

    public function shouldThrowEarly(): bool
    {
        return $this->throwEarly;
    }

    public function setThrowEarly(bool $flag): void
    {
        $this->throwEarly = $flag;
    }

    //

    public function translate(string $message, array $context = []): string
    {
        return $this->tr->translate($message, $context);
    }

    //

    private static Assert $instance;

    public static function getInstance(): self
    {
        return self::$instance ?? (self::$instance = new self());
    }

    //

    public static function value($value, ?string $attributeName = null, bool $isValueSensitive = false): AssertBuilder
    {
        return new AssertBuilder(self::getInstance(), $value, $attributeName, $isValueSensitive);
    }
}
