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
			type: map
			properties:
				name:
					type: string
					optional: true
NEON
);


$validator = new Validator($config['basic']);

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
	["Missing key in '/name'", "Missing key in '/email'"],
	$validator->validate([])->getErrors()
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


$validator = new Validator($config['advanced']);

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
		'another' => [],
		'bar' => 3,
	])->getErrors()
);

Assert::same(
	["Missing key in '/deep/name'"],
	$validator->validate([
		'deep' => [],
		'another' => [],
	])->getErrors()
);
