<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: int
nullable:
	type: int|null
NEON
);


$validator = new Validator($config['basic']);

Assert::same(
	[],
	$validator->validate(1)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'int'; got 'float'"],
	$validator->validate(1.0)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'int'; got 'string'"],
	$validator->validate("string")->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'int'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================


$validator = new Validator($config['nullable']);

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'int|null'; got 'array'"],
	$validator->validate([])->getErrors()
);
