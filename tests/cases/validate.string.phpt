<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: string
nullable:
	type: string|null
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same(
	[],
	$validator->validate('string')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'string'; got 'float'"],
	$validator->validate(1.0)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'string'; got 'int'"],
	$validator->validate(2)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'string'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'string|null'; got 'array'"],
	$validator->validate([])->getErrors()
);
