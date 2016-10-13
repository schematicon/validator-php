<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: anyOf
	options:
		-
			type: int
		-
			type: string
		-
			type: map
			properties:
				name:
					type: string
					optional: true
		-
			type: map
			properties:
				person:
					type: map
					properties:
						age:
							type: int
NEON
);


$validator = new Validator($config['basic']);

Assert::same(
	[],
	$validator->validate(1)->getErrors()
);

Assert::same(
	[],
	$validator->validate('string')->getErrors()
);

Assert::same(
	[],
	$validator->validate([])->getErrors()
);

Assert::same(
	[],
	$validator->validate(['name' => 'jan'])->getErrors()
);

Assert::same(
	[],
	$validator->validate(['person' => ['age' => 2]])->getErrors()
);

Assert::same(
	[
		"Wrong data type in '/'; expected validity at least for one of sub-schemas:",
		"- 0: Wrong data type in '/'; expected 'int'; got 'map'",
		"- 1: Wrong data type in '/'; expected 'string'; got 'map'",
		"- 2: Wrong data type in '/name'; expected 'string'; got 'int'",
		"- 3: Wrong data type in '/person/age'; expected 'int'; got 'string'",
	],
	$validator->validate(['name' => 2, 'person' => ['age' => 'string']])->getErrors()
);
