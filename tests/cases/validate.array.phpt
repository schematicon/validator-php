<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: array
	item:
		type: int
nullable:
	type: array|null
	item:
		type: int
advanced:
	type: array
	minCount: 1
	maxCount: 3
	item:
		type: map
		properties:
			name:
				type: string|null
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same(
	[],
	$validator->validate([1, 2, 4])->getErrors()
);

Assert::same(
	[],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'array'; got 'map'"],
	$validator->validate([1 => 2])->getErrors()
);

// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));

Assert::same(
	[],
	$validator->validate([1, 2, 4])->getErrors()
);

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['advanced']));

Assert::same(
	[],
	$validator->validate([
		['name' => null],
		['name' => null],
	])->getErrors()
);


Assert::same(
	["Wrong maximum items count in '/'; expected '3'; got '4'"],
	$validator->validate([
		['name' => null],
		['name' => null],
		['name' => null],
		['name' => null],
	])->getErrors()
);

Assert::same(
	["Wrong minimum items count in '/'; expected '1'; got '0'"],
	$validator->validate([])->getErrors()
);
