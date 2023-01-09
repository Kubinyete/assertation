<?php

use Kubinyete\Assertation\Assert;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

$result = Assert::value('  test  ', 'meuatributo')->null()->or()->asUppercase()->asTrim()->equals('TEST')->or()->equals(1)->or()->applyRules('ipv4');

$ok = $result->isValid();
$errs = $result->errors();

dd($ok, $errs);
