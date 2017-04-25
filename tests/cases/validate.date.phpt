<?php

namespace SchematiconTests;

use DateTimeImmutable;
use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: date
nullable:
	type: date|null
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same([], $validator->validate('2016-01-01')->getErrors());
Assert::same([], $validator->validate('0000-01-01')->getErrors());
Assert::same([], $validator->validate('9999-01-01')->getErrors());

Assert::same(
	["Wrong data type in '/'; expected 'date'; got 'float'"],
	$validator->validate(1.0)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'date'; got 'string'"],
	$validator->validate("string")->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'date'; got 'string'"],
	$validator->validate("2016-01-01X")->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'date'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'date'; got 'bool'"],
	$validator->validate(true)->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected valid ISO 8601 date as 'YYYY-MM-DD'."],
	$validator->validate('9999-99-99')->getErrors()
);

Assert::type('string', $validator->validate('2016-01-01')->getData());


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	[],
	$validator->validate('2016-01-01')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'date|null'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));
$validator->coerceStringToDateTimeImmutable = true;

Assert::type(DateTimeImmutable::class, $validator->validate('2016-01-01')->getData());
