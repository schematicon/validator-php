<?php

namespace SchematiconTests;

use DateTimeImmutable;
use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: localdatetime
nullable:
	type: localdatetime|null
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same([], $validator->validate('2016-01-01T12:00:00')->getErrors());
Assert::same([], $validator->validate('2016-01-01 12:00:00')->getErrors());
Assert::same([], $validator->validate('2016-01-01T12:00:00.12')->getErrors());

Assert::same(
	["Wrong data type in '/'; expected 'localdatetime'; got 'float'"],
	$validator->validate(1.0)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'localdatetime'; got 'string'"],
	$validator->validate("string")->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'localdatetime'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'localdatetime'; got 'bool'"],
	$validator->validate(true)->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected valid ISO 8601 datetime without timezone from as 'YYYY-MM-DDThh:mm:ss'."],
	$validator->validate('9999-99-99T99:99:99')->getErrors()
);

Assert::type('string', $validator->validate('2016-01-01T12:00:00')->getData());


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	[],
	$validator->validate('2016-01-01T12:00:00')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'localdatetime|null'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));
$validator->coerceStringToDateTimeImmutable = true;

Assert::type(DateTimeImmutable::class, $validator->validate('2016-01-01T12:00:00')->getData());
