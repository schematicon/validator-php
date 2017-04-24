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
	minValue: 0
	maxValue: 100
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

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

Assert::same(
	["Wrong data type in '/'; expected 'int'; got 'bool'"],
	$validator->validate(true)->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	[],
	$validator->validate(0)->getErrors()
);

Assert::same(
	[],
	$validator->validate(100)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'int|null'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected number of maximal value '100'; got value '101'"],
	$validator->validate(101)->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected number of minimal value '0'; got value '-1'"],
	$validator->validate(-1)->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['basic']));
$validator->coerceStringToInt = true;

$result = $validator->validate('1');
Assert::true($result->isValid());
Assert::same(1, $result->getData());

$result = $validator->validate('-2');
Assert::true($result->isValid());
Assert::same(-2, $result->getData());

$result = $validator->validate('0');
Assert::true($result->isValid());
Assert::same(0, $result->getData());

Assert::same(
	["Wrong data type in '/'; expected 'int'; got 'string'"],
	$validator->validate('2.0')->getErrors()
);
