<?php

namespace SchematiconTests;

use DateTimeImmutable;
use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: email
nullable:
	type: email|null
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same([], $validator->validate('email@domain.com')->getErrors());
Assert::same([], $validator->validate('firstname.lastname@domain.com')->getErrors());
Assert::same([], $validator->validate('email@subdomain.domain.com')->getErrors());
Assert::same([], $validator->validate('firstname+lastname@domain.com')->getErrors());
Assert::same([], $validator->validate('"email"@domain.com')->getErrors());
Assert::same([], $validator->validate('1234567890@domain.com')->getErrors());
Assert::same([], $validator->validate('email@domain-one.com')->getErrors());
Assert::same([], $validator->validate('_______@domain.com')->getErrors());
Assert::same([], $validator->validate('email@domain.name')->getErrors());
Assert::same([], $validator->validate('email@domain.co.jp')->getErrors());
Assert::same([], $validator->validate('firstname-lastname@domain.com')->getErrors());


Assert::same(
	["Wrong data type in '/'; expected 'email'; got 'float'"],
	$validator->validate(1.0)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'email'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'email'; got 'bool'"],
	$validator->validate(true)->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected valid email address (RFC 822)."],
	$validator->validate('plainaddress')->getErrors()
);
Assert::same(
	["Wrong value in '/'; expected valid email address (RFC 822)."],
	$validator->validate('email@domain.com (Joe Smith)')->getErrors()
);
Assert::same(
	["Wrong value in '/'; expected valid email address (RFC 822)."],
	$validator->validate('email@domain..com')->getErrors()
);

Assert::type('string', $validator->validate('email@domain.com')->getData());


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['nullable']));

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	[],
	$validator->validate('email@domain.com')->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'email|null'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================

