<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\SchemaValidator;
use Tester\Assert;


require_once __DIR__ . '/../bootstrap.php';


$validator = new SchemaValidator();


Assert::same(
	[],
	$validator->validate([
		'type' => 'int'
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'type' => 'int|float|string'
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'type' => 'map',
		'properties' => [
			'name' => ['type' => 'string'],
		],
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'type' => 'map',
		'regexpProperties' => [
			'name' => ['type' => 'string'],
		],
		'properties' => [
			'name' => ['type' => 'string'],
		],
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate([
		'type' => 'string',
		'regexp' => '.+',
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate('int')->getErrors()
);

Assert::same(
	[],
	$validator->validate(Neon::decode(file_get_contents(__DIR__ . '/../../schema/schema.neon')))->getErrors()
);
