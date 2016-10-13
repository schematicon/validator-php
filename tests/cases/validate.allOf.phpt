<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: allOf
	options:
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
					optional: true
NEON
);


$validator = new Validator($config['basic']);

Assert::same(
	[],
	$validator->validate([
		'name' => 'john'
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'name' => 'john',
		'age' => 3
	])->getErrors()
);

Assert::same(
	[
		"Wrong data type in '/'; expected validity for all sub-schemas; invalid for:",
		"- 0: Missing key in '/name'",
	],
	$validator->validate([])->getErrors()
);

Assert::same(
	[
		"Wrong data type in '/'; expected validity for all sub-schemas; invalid for:",
		"- 1: Wrong data type in '/age'; expected 'int'; got 'string'",
	],
	$validator->validate([
		'name' => 'john',
		'age' => '3',
	])->getErrors()
);
