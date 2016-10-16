<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	enum: [1, 'test', true, null]
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same(
	[],
	$validator->validate(true)->getErrors()
);


Assert::same(
	[],
	$validator->validate(1)->getErrors()
);


Assert::same(
	[],
	$validator->validate('test')->getErrors()
);

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected value from [1,\"test\",true,null]; got type 'bool'"],
	$validator->validate(false)->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected value from [1,\"test\",true,null]; got type 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected value from [1,\"test\",true,null]; got type 'map'"],
	$validator->validate((object) [])->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected value from [1,\"test\",true,null]; got type 'string'"],
	$validator->validate('wrong')->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected value from [1,\"test\",true,null]; got type 'int'"],
	$validator->validate(2)->getErrors()
);
