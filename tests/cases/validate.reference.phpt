<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\SchemaNotFound;
use Schematicon\Validator\Validator;
use Schematicon\Validator\ValidatorException;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
reference: integer_reference
NEON
);

$referencedConfig = Neon::decode(<<<NEON
type: int
NEON
);


$validator = new Validator(prepareSchema($config), false, function () use ($referencedConfig) {
	return prepareSchema($referencedConfig);
});

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

Assert::exception(function () use ($config) {
		$validatorWithoutLoader = new Validator($config, false);
		$validatorWithoutLoader->validate(1);
	},
	ValidatorException::class,
	"Missing referenced scheme loader when trying to load: 'integer_reference'"
);

Assert::exception(function () use ($config) {
	$validatorWithoutLoader = new Validator($config, false, function () {
		return null;
	});
	$validatorWithoutLoader->validate(1);
},
	SchemaNotFound::class,
	"Reference schema loader cannot load schema with 'integer_reference' name."
);

