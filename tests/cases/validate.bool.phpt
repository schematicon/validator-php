<?php

namespace NextrasTests\Schematicon;

use Nette\Neon\Neon;
use Nextras\Schematicon\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: bool
nullable:
	type: bool|null
NEON
);


$validator = new Validator($config['basic']);

Assert::same(
	[],
	$validator->validate(true)->getErrors()
);

Assert::same(
	[],
	$validator->validate(false)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'bool'; got 'int'"],
	$validator->validate(1)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'bool'; got 'string'"],
	$validator->validate("string")->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'bool'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================


$validator = new Validator($config['nullable']);

Assert::same(
	[],
	$validator->validate(null)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'bool|null'; got 'array'"],
	$validator->validate([])->getErrors()
);
