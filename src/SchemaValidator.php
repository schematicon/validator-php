<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/validator-php
 */

namespace Schematicon\Validator;

use Nette\Neon\Neon;


class SchemaValidator
{
	/** @var Validator */
	private $validator;


	public function __construct(bool $failFast = false)
	{
		$schema = Neon::decode(file_get_contents(__DIR__ . '/../schema/schema.neon'));
		$normalizedSchema = (new Normalizer())->normalize($schema);
		$this->validator = new Validator($normalizedSchema, $failFast, function ($referenceName) use ($normalizedSchema) {
			return $referenceName === '#' ? $normalizedSchema : null;
		});
	}


	/**
	 * @param  string|array $schema
	 */
	public function validate($schema): Result
	{
		return $this->validator->validate($schema);
	}
}
