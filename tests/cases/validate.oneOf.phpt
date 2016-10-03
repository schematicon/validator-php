<?php

namespace NextrasTests\Schematicon;

use Nette\Neon\Neon;
use Nextras\Schematicon\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: oneOf
	options:
		-
			type: map
			keys:
				name:
					type: string
		-
			type: map
			keys:
				age:
					type: int
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
		"- 0: Missing key in '/name'",
		"- 1: Missing key in '/age'",
	],
	$validator->validate([])->getErrors()
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
