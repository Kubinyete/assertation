<?php

use Kubinyete\Assertation\Assert;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

$result = Assert::value(6, 'meuatributo')->null()->or()->asUppercase()->asTrim()->eq('TEST')->or()->eq(1)->or()->rules('string.lgt:5|integer.gt:5');

$ok = $result->valid();
$errs = $result->errors();
$data = $result->get();

dd($ok, $errs, $data);
