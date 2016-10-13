<?php

/**
 * This file is part of the Nextras\Schematicon library.
 * @license    MIT
 * @link       https://github.com/nextras/schematicon
 */

namespace Nextras\Schematicon;


final class Normalizer
{

	public function normalize($schema)
	{
		$schema = $this->unwrapShortTypes($schema);
		return $schema;
	}

	private function unwrapShortTypes($schema, $keyName = null)
	{
		if (in_array($keyName, ['reference'])) {
			return $schema;
		}

		if (is_string($schema)) {
			return [
				'type' => $schema
			];
		}

		if (is_array($schema)) {
			$types = explode('|', $schema['type']);
			foreach ($types as $type) {
				if ($type === 'array') {
					$schema['item'] = $this->unwrapShortTypes($schema['item']);
				} elseif ($type === 'map') {
					foreach ($schema['keys'] as $keyName => $keyValue) {
						$schema['keys'][$keyName] = $this->unwrapShortTypes($keyValue, $keyName);
					}
				} elseif (in_array($type, ['allOf', 'anyOf', 'oneOf'])) {
					$options = array_map(function ($option) {
							return $this->unwrapShortTypes($option);
						},
						$schema['options']
					);
					$schema['options'] = $options;
				}
			}
		}

		return $schema;
	}
}
