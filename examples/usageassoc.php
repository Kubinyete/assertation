<?php

use Kubinyete\Assertation\Assert;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

$data = [
    'name' => null,
    'age' => 19,
    'ipaddress' => '192.168.1.1'
];

$data = Assert::value($data)->applyAssocRules([
    'name' => 'notNull|string|asTrim|asUppercase|lengthBetween:2,255',
    'age' => 'null|or|integer|greaterThan:18',
    'ipaddress' => 'null|or|ipv4'
]);

dd($data->get());
