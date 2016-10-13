<?php

namespace NextrasTests\Schematicon;

use Nette\Neon\Neon;
use Schematicon\Validator\Normalizer;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$schema = Neon::decode(<<<NEON
full:
	type: map
	properties:
		reference: some_reference
		int:
			type: int
		bool:
			type: bool
		array:
			type: array
			item:
				type: bool|null
		map:
			type: map|null
			properties:
				name:
					type: string
				age:
					type: int|null
		anyOf:
			type: anyOf
			options:
				-
					type: int
				-
					type: string

short:
	type: map
	properties:
		reference: some_reference
		int: int
		bool: bool
		array:
			type: array
			item: bool|null
		map:
			type: map|null
			properties:
				name: string
				age: int|null
		anyOf:
			type: anyOf
			options: [int, string]
NEON
);

$validator = new Normalizer();

Assert::same(
	$schema['full'],
	$validator->normalize($schema['full'])
);

Assert::same(
	$schema['full'],
	$validator->normalize($schema['short'])
);

