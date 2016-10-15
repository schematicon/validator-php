<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$config = Neon::decode(<<<NEON
basic:
	type: map
	properties:
		name:
			type: string|null
		email:
			type: string
advanced:
	type: map
	properties:
		deep:
			type: map
			properties:
				name: 
					type: string|null
		another:
			type: map|null
			properties:
				name:
					type: string
					optional: true
regexp:
	type: map
	regexpProperties:
		'.+':
			type: int|string
NEON
);


$validator = new Validator(prepareSchema($config['basic']));

Assert::same(
	[],
	$validator->validate([
		'name' => 'Jan',
		'email' => 'jan@skrasek.com',
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'name' => null,
		'email' => 'jan@skrasek.com',
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate((object) [
		'name' => null,
		'email' => 'jan@skrasek.com',
	])->getErrors()
);

Assert::same(
	["Missing key in '/name'", "Missing key in '/email'"],
	$validator->validate((object) [])->getErrors()
);

Assert::same(
	["Wrong data type in '/email'; expected 'string'; got 'null'"],
	$validator->validate([
		'name' => null,
		'email' => null,
	])->getErrors()
);

Assert::same(
	["Wrong data type in '/email'; expected 'string'; got 'int'"],
	$validator->validate([
		'name' => null,
		'email' => 2,
	])->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['advanced']));

Assert::same(
	[],
	$validator->validate([
		'deep' => ['name' => ''],
		'another' => ['name' => ''],
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'deep' => ['name' => ''],
		'another' => null,
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'deep' => ['name' => ''],
		'another' => (object) [],
	])->getErrors()
);

Assert::same(
	["Missing key in '/deep/name'"],
	$validator->validate([
		'deep' => (object) [],
		'another' => (object) [],
	])->getErrors()
);


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['regexp']));

Assert::same(
	[],
	$validator->validate([
		'prop1' => 'string',
		'prop2' => 2,
	])->getErrors()
);

Assert::same(
	["Wrong data type in '/.+'; expected 'int|string'; got 'bool'"],
	$validator->validate([
		'prop1' => 'string',
		'prop2' => false,
	])->getErrors()
);
