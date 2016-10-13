<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: const
	value: 1
advanced:
	type: map
	keys:
		name:
			type: const
			value: jan
NEON
);


$validator = new Validator($config['basic']);

Assert::same(
	[],
	$validator->validate(1)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected '1'; got '1'"], // todo: better error message
	$validator->validate(1.0)->getErrors()
);


// =====================================================================================================================


$validator = new Validator($config['advanced']);

Assert::same(
	[],
	$validator->validate(['name' => 'jan'])->getErrors()
);

Assert::same(
	["Wrong data type in '/name'; expected 'jan'; got 'peter'"],
	$validator->validate(['name' => 'peter'])->getErrors()
);
