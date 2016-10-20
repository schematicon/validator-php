<?php

/**
 * This file is part of the Nextras\Schematicon library.
 * @license    MIT
 * @link       https://github.com/nextras/schematicon
 */

namespace Schematicon\Validator;


class Normalizer
{
	public function normalize($schema): array
	{
		if (is_string($schema)) {
			return [
				'type' => $schema
			];
		}

		if (is_array($schema)) {
			if (isset($schema['reference']) || isset($schema['enum'])) {
				return $schema;

			} elseif (isset($schema['anyOf'])) {
				$schema['anyOf'] = array_map([$this, 'normalize'], $schema['anyOf']);
				return $schema;

			} elseif (isset($schema['oneOf'])) {
				$schema['oneOf'] = array_map([$this, 'normalize'], $schema['oneOf']);
				return $schema;

			} elseif (isset($schema['allOf'])) {
				$schema['allOf'] = array_map([$this, 'normalize'], $schema['allOf']);
				return $schema;

			} else {
				$types = explode('|', $schema['type']);
				foreach ($types as $type) {
					if ($type === 'map') {
						$properties = [];
						foreach ($schema['properties'] ?? [] as $propName => $propValue) {
							$propValue = $this->normalize($propValue);
							if (($propName[0] ?? '') === '?') {
								$propName = substr($propName, 1);
								$propValue['optional'] = true;
							}
							$properties[$propName] = $propValue;
						}
						$schema['properties'] = $properties;
						foreach ($schema['regexpProperties'] ?? [] as $propName => $propValue) {
							$schema['regexpProperties'][$propName] = $this->normalize($propValue);
						}

					} elseif ($type === 'array') {
						$schema['item'] = $this->normalize($schema['item']);
					}
				}
				return $schema;
			}
		}

		return $schema;
	}
}
