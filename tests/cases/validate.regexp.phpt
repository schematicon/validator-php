<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: string
	regexp: '~john~'
nullable:
	type: string|null
	regexp: '~^[a-z_]+$~'
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same(
	[],
	$validator->validate('john')->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected value matching '~john~' regexp; got type 'string'"],
	$validator->validate('jo')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'string'; got 'int'"],
	$validator->validate(1)->getErrors()
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
	[],
	$validator->validate('abc_def')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'string|null'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected value matching '~^[a-z_]+$~' regexp; got type 'string'"],
	$validator->validate('ABC_DEF')->getErrors()
);
