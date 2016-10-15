<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	oneOf:
		-
			type: map
			properties:
				name:
					type: string
		-
			type: map
			properties:
				age:
					type: int
advanced:
	oneOf:
		-
			type: map
			properties:
				type:
					type: const
					value: poi
				name:
					type: string
				destination:
					type: int
		-
			type: map
			properties:
				type:
					type: const
					value: destination
				name:
					type: string
				id:
					type: int
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same(
	[],
	$validator->validate([
		'name' => 'john'
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'age' => 3
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'name' => 'john',
		'age' => '3',
	])->getErrors()
);

Assert::same(
	[
		"Wrong data type in '/'; expected validity for just one sub-schema:",
		"- 0: Missing 'name' key in '/' path",
		"- 1: Missing 'age' key in '/' path",
	],
	$validator->validate((object) [])->getErrors()
);

Assert::same(
	[
		"Wrong data type in '/'; expected validity for only one sub-schema; valid for schemas number: 0, 1.",
	],
	$validator->validate([
		'name' => 'john',
		'age' => 3,
	])->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['advanced']));

Assert::same(
	[],
	$validator->validate([
		'type' => 'poi',
		'name' => 'Liberty Square',
		'destination' => 3
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'type' => 'destination',
		'name' => 'Brno',
		'id' => 3
	])->getErrors()
);

Assert::same(
	[
		"Wrong data type in '/'; expected validity for just one sub-schema:",
		"- 0: Missing 'destination' key in '/' path", // todo: better error message with no fail-fast option
		"- 1: Missing 'id' key in '/' path",
	],
	$validator->validate([
		'type' => 'destination',
		'name' => 'Brno',
	])->getErrors()
);
