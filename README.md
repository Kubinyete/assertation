# Assertation

Assertation is a free and simple to use validation library, it's built to allow a more expressive syntax while applying
data validation and mantaining an assert-oriented approach.

# Getting started

To get started, please use our own package directly with Composer:

```sh
composer require kubinyete/assertation
```

# Usage

First of all, we can start understanding the objective of this approach by looking at some examples and comparisons,
below are some equivalent validations with and without `assertation`.

```php
$data = '   Spaced   text   ';

// This validation:
$validated = ($data = trim($data)) && strlen($data) > 2;

if (!$validated) {
    throw new UnexpectedValueException('Data is required with at least 2 characters');
}

// Is equivalent to:
$data = Assert::value($data)->asTrim()->lgt(2)->validate()->get();

// And also equivalent to:
$data = Assert::value($data)->rules('asTrim;lgt,2')->get();
```

The following examples are a more complex validation, for simple ones it's easier to do natively, but when our validation
rules become more complex, `assertation` can easily validate and modify our data in a more expressive way.

```php
$data = Assert::value($data)->rules('null|str;asTrim;asUppercase;asTruncate,100;lgt,1')->get();
// Data can either be null or a string that has a minimum of 1 character and has every character in uppercase
// while limiting the maximum size to 100 characters with a ellipsis cutoff.

$data = Assert::value($data)->rules('null|float|asDecimal')->get();
// Data can either be null, a float or a string with a decimal number (Ex: 123.23).

$data = Assert::value($data)->null()->or()->asUppercase()->asTrim()->in(['HELLO', 'WORLD'])->get();
// Data can either be null or be 'hello' or 'world' with case insensitivity, resulting only in a upper case result.
```

## API Documentation

* Kubinyete
    * Kubinyete\Assertation
        * [Assert](/docs/Kubinyete-Assertation-Assert.md)
        * [AssertBuilder](/docs/Kubinyete-Assertation-AssertBuilder.md)
        * Kubinyete\Assertation\Exception
            * [AssertException](/docs/Kubinyete-Assertation-Exception-AssertException.md)
            * [ValidationException](/docs/Kubinyete-Assertation-Exception-ValidationException.md)
        * Kubinyete\Assertation\Localization
            * [BaseTranslator](/docs/Kubinyete-Assertation-Localization-BaseTranslator.md)
            * [LangTranslator](/docs/Kubinyete-Assertation-Localization-LangTranslator.md)
            * [NullTranslator](/docs/Kubinyete-Assertation-Localization-NullTranslator.md)
            * [TranslatorInterface](/docs/Kubinyete-Assertation-Localization-TranslatorInterface.md)
        * Kubinyete\Assertation\Util
            * [ArrayUtil](/docs/Kubinyete-Assertation-Util-ArrayUtil.md)
            * [Luhn](/docs/Kubinyete-Assertation-Util-Luhn.md)
            * [StringInterpolation](/docs/Kubinyete-Assertation-Util-StringInterpolation.md)

