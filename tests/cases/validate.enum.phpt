<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: enum
	values: [1, 'test', true, null]
nullable:
	type: enum|null
	values: [1, 'test', true]
NEON
);


$validator = new Validator($config['basic']);

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
	["Wrong data type in '/'; expected 'enum'; got 'bool'"],
	$validator->validate(false)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'enum'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'enum'; got 'map'"],
	$validator->validate((object) [])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'enum'; got 'string'"],
	$validator->validate('wrong')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'enum'; got 'int'"],
	$validator->validate(2)->getErrors()
);



// =====================================================================================================================


$validator = new Validator($config['nullable']);

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

