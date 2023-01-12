<?php

namespace Kubinyete\Assertation\Exception;

use RuntimeException;
use Throwable;

class ValidationException extends AssertException
{
    protected array $errors;

    public function __construct(string $message, array $errors)
    {
        $this->errors = $errors;
        parent::__construct($message . PHP_EOL . $this->getErrorsSimplifiedString());
    }

    protected function getErrorsString(): string
    {
        $data = '';

        foreach ($this->errors as $attr => $errs) {
            $errs = !is_array($errs) ? [$errs] : $errs;

            foreach ($errs as $e) {
                $data .= "Attribute $attr > $e" . PHP_EOL;
            }
        }

        return $data;
    }

    protected function getErrorsSimplifiedString(): string
    {
        $data = '';

        foreach ($this->errors as $attr => $errs) {
            $errs = !is_array($errs) ? [$errs] : $errs;

            foreach ($errs as $e) {
                $data .= $e . PHP_EOL;
            }
        }

        return $data;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
