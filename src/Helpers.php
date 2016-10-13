<?php

/**
 * This file is part of the Nextras\Schematicon library.
 * @license    MIT
 * @link       https://github.com/nextras/schematicon
 */

namespace Nextras\Schematicon;


class Helpers
{
	public static function isList($value): bool
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
		];

		$type = gettype($var);
		if ($type === 'array') {
			return Helpers::isList($var) ? 'array' : 'map';
		} else {
			return $map[$type] ?? $type;
		}
	}
}
