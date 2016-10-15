<?php

/**
 * This file is part of the Nextras\Schematicon library.
 * @license    MIT
 * @link       https://github.com/nextras/schematicon
 */

namespace Schematicon\Validator;


final class Normalizer
{
	public function normalize($schema)
	{
		$schema = $this->unwrapShortTypes($schema);
		return $schema;
	}


	private function unwrapShortTypes($schema)
	{
		if (is_string($schema)) {
			return [
				'type' => $schema
			];
		}

		if (is_array($schema)) {
			if (isset($schema['reference'])) {
				return $schema;

			} elseif (isset($schema['anyOf'])) {
				$schema['anyOf'] = array_map([$this, 'unwrapShortTypes'], $schema['anyOf']);
				return $schema;

			} elseif (isset($schema['oneOf'])) {
				$schema['oneOf'] = array_map([$this, 'unwrapShortTypes'], $schema['oneOf']);
				return $schema;

			} elseif (isset($schema['allOf'])) {
				$schema['allOf'] = array_map([$this, 'unwrapShortTypes'], $schema['allOf']);
				return $schema;

			} else {
				$types = explode('|', $schema['type']);
				foreach ($types as $type) {
					if ($type === 'map') {
						foreach ($schema['properties'] ?? [] as $propName => $propValue) {
							$schema['properties'][$propName] = $this->unwrapShortTypes($propValue);
						}
						foreach ($schema['regexp_properties'] ?? [] as $propName => $propValue) {
							$schema['regexp_properties'][$propName] = $this->unwrapShortTypes($propValue);
						}

					} elseif ($type === 'array') {
						$schema['item'] = $this->unwrapShortTypes($schema['item']);
					}
				}
				return $schema;
			}
		}

		return $schema;
	}
}
