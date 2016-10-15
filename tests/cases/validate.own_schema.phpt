<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Normalizer;
use Schematicon\Validator\Validator;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$schema = Neon::decode(file_get_contents(__DIR__ . '/../../schema/schema.neon'));
$normalizer = new Normalizer();
$normalizedSchema = $normalizer->normalize($schema);
$validator = new Validator($normalizedSchema, false, function ($path) use ($normalizedSchema) {
	if ($path === '#') {
		return $normalizedSchema;
	} else {
		throw new \RuntimeException('Unknown reference');
	}
});


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
		'regexp_properties' => [
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
		'type' => 'regexp',
		'value' => '.+',
	])->getErrors()
);

Assert::same(
	[],
	$validator->validate('int')->getErrors()
);

Assert::same(
	[],
	$validator->validate($schema)->getErrors()
);
