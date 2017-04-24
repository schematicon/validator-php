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
				?surname: string
regexp:
	type: map
	regexpProperties:
		'.+':
			type: int|string
coercion:
	type: map
	properties:
		live: bool
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
	["Missing 'name' key in '/' path", "Missing 'email' key in '/' path"],
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
		'deep' => ['name' => '', 'surname' => ''],
		'another' => (object) [],
	])->getErrors()
);

Assert::same(
	["Missing 'name' key in '/deep/' path"],
	$validator->validate([
		'deep' => (object) [],
		'another' => (object) [],
	])->getErrors()
);

Assert::same(
	["Wrong data type in '/another/surname'; expected 'string'; got 'int'"],
	$validator->validate([
		'deep' => ['name' => ''],
		'another' => ['surname' => 3],
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


// =====================================================================================================================


$validator = new Validator(prepareSchema($config['coercion']));
$validator->coerceStringToBool = true;

$result = $validator->validate(['live' => '1']);
Assert::true($result->isValid());
Assert::same(true, $result->getData()['live']);


$result = $validator->validate((object) ['live' => '1']);
Assert::true($result->isValid());
Assert::same(true, $result->getData()->live);
