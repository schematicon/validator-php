<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Normalizer;
use Schematicon\Validator\Validator;
use Tester\Environment;

require_once __DIR__ . '/../vendor/autoload.php';
Environment::setup();

header('Content-type: text/plain');
putenv('ANSICON=TRUE');


function prepareSchema(array $schema): array
{
	$normalizer = new Normalizer();
	$schemaSchema = Neon::decode(file_get_contents(__DIR__ . '/../schema/schema.neon'));
	$schemaSchema = $normalizer->normalize($schemaSchema);
	$validator = new Validator($schemaSchema, false, function () use ($schemaSchema) {
		return $schemaSchema;
	});
	if (!$validator->validate($schema)->isValid()) {
		throw new \RuntimeException('Invalid schema for testing');
	}
	return $normalizer->normalize($schema);
}
