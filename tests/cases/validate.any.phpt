<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: any
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same(
	[],
	$validator->validate(1)->getErrors()
);

Assert::same(
	[],
	$validator->validate(1.0)->getErrors()
);

Assert::same(
	[],
	$validator->validate("string")->getErrors()
);

Assert::same(
	[],
	$validator->validate([])->getErrors()
);

Assert::same(
	[],
	$validator->validate((object) [])->getErrors()
);

Assert::same(
	[],
	$validator->validate(true)->getErrors()
);

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);
