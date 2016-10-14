<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: regexp
	value: '~john~'
nullable:
	type: regexp|null
	value: '~^[a-z_]+$~'
NEON
);


$validator = new Validator($config['basic']);

Assert::same(
	[],
	$validator->validate('john')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'regexp'; got 'string'"],
	$validator->validate('jo')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'regexp'; got 'int'"],
	$validator->validate(1)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'regexp'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================


$validator = new Validator($config['nullable']);

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	[],
	$validator->validate('abc_def')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'regexp|null'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'regexp|null'; got 'string'"],
	$validator->validate('ABC_DEF')->getErrors()
);
