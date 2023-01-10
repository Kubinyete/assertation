<?php

use Kubinyete\Assertation\Assert;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

$result = Assert::value('23,223', 'cpforcnpj')->asCpf()->or()->asCnpj()->or()->null()->or()->asDecimal();

$ok = $result->valid();
$errs = $result->errors();
$data = $result->get();

dd($ok, $errs, $data);
