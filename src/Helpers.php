<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/validator-php
 */

namespace Schematicon\Validator;


class Helpers
{
	public static function isArray($value): bool
	{
		return is_array($value) && (!$value || array_keys($value) === range(0, count($value) - 1));
	}


	public static function getVariableType($var): string
	{
		static $map = [
			'NULL' => 'null',
			'integer' => 'int',
			'double' => 'float',
			'boolean' => 'bool',
			'object' => 'map',
		];

		$type = gettype($var);
		if ($type === 'array') {
			return Helpers::isArray($var) ? 'array' : 'map';
		} else {
			return $map[$type] ?? $type;
		}
	}
}
