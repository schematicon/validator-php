<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/validator-php
 */

namespace Schematicon\Validator;


final class Validator
{
	private $schema;

	/** @var bool */
	private $failFast;

	/** @var callable */
	private $referenceCallback;


	public function __construct($schema, bool $failFast = false, callable $referenceCallback = null)
	{
		$this->schema = $schema;
		$this->failFast = $failFast;
		$this->referenceCallback = $referenceCallback;
	}


	public function validate($data)
	{
		$errors = [];
		$stack = [[$this->schema, $data, '/']];
		while (list ($schema, $node, $path) = array_pop($stack)) {
			if (isset($schema['reference']) && is_string($schema['reference'])) {
				$isValid = $this->validateReference($node, $schema['reference'], $path, $stack);

			} elseif (isset($schema['anyOf'])) {
				$isValid = $this->validateAnyOf($node, $schema['anyOf'], $path, $errors);

			} elseif (isset($schema['oneOf'])) {
				$isValid = $this->validateOneOf($node, $schema['oneOf'], $path, $errors);

			} elseif (isset($schema['allOf'])) {
				$isValid = $this->validateAllOf($node, $schema['allOf'], $path, $errors);

			} elseif ($schema['type'] === 'const') {
				$isValid = $node === $schema['value'];
				if (!$isValid) {
					$wrongPath = $path === '/' ? $path : rtrim($path, '/');
					array_unshift($errors, "Wrong data type in '$wrongPath'; expected '$schema[value]'; got '{$node}'");
				}

			} else {
				$isValid = null;
				$types = explode('|', $schema['type']);
				foreach ($types as $type) {
					if ($type === 'any') {
						$isValid = true;
						break;
					} elseif ($type === 'null') {
						if ($node === null) {
							$isValid = true;
							break;
						}
					} elseif ($type === 'string') {
						if (is_string($node)) {
							$isValid = true;
							break;
						}
					} elseif ($type === 'bool') {
						if (is_bool($node)) {
							$isValid = true;
							break;
						}
					} elseif ($type === 'int') {
						if (is_int($node)) {
							$isValid = true;
							break;
						}
					} elseif ($type === 'float') {
						if (is_float($node)) {
							$isValid = true;
							break;
						}
					} elseif ($type === 'regexp') {
						if (is_string($node) && preg_match($schema['value'], $node) === 1) {
							$isValid = true;
							break;
						}
					} elseif ($type === 'enum') {
						if (!(is_array($node) || $node instanceof \stdClass) && in_array($node, $schema['values'], true)) {
							$isValid = true;
							break;
						}
					} elseif ($type === 'array') {
						if (is_array($node) && Helpers::isArray($node)) {
							$isValid = $this->validateItems($node, $schema, $path, $stack, $errors);
							if ($isValid) {
								break;
							}
						}

					} elseif ($type === 'map') {
						if ((is_array($node) || $node instanceof \stdClass) && !Helpers::isArray($node)) {
							$isValid = $this->validateInnerProperties($node, $schema, $path, $stack, $errors);
							if ($isValid) {
								break;
							}
						}
					}
				}

				if ($isValid === null) {
					$wrongPath = $path === '/' ? $path : rtrim($path, '/');
					$wrongType = Helpers::getVariableType($node);
					$errors[] = "Wrong data type in '$wrongPath'; expected '$schema[type]'; got '{$wrongType}'";
					$isValid = false;
				}
			}

			if ($isValid === false && $this->failFast) {
				break;
			}
		}

		return new Result($errors);
	}


	private function validateInnerProperties($node, $schema, $path, & $stack, & $errors)
	{
		$isValid = true;
		$node = (array) $node; // may be a stdClass
		foreach ($schema['properties'] ?? [] as $propName => $propSchema) {
			if (isset($node[$propName]) || array_key_exists($propName, $node)) {
				$stack[] = [$propSchema, $node[$propName], "$path$propName/"];

			} elseif (!isset($propSchema['optional']) || !$propSchema['optional']) {
				$errors[] = "Missing key in '$path$propName'";
				$isValid = false;
				if ($this->failFast) {
					break;
				}
			}
		}
		foreach ($schema['regexp_properties'] ?? [] as $propName => $propSchema) {
			$expression = "~$propName~";
			foreach ($node as $nodeKey => $nodeValue) {
				if (preg_match($expression, $nodeKey) !== 1) {
					continue;
				}

				$stack[] = [$propSchema, $nodeValue, "$path$propName/"];
			}
		}

		return $isValid;
	}


	private function validateItems($node, $schema, $path, & $stack, & $errors)
	{
		$isValid = true;

		if (isset($schema['max_count'])) {
			if (count($node) > $schema['max_count']) {
				$wrongCount = count($node);
				$wrongPath = $path === '/' ? $path : rtrim($path, '/');
				$errors[] = "Wrong maximum items count in '$wrongPath'; expected '$schema[max_count]'; got '{$wrongCount}'";
				$isValid = false;
				if ($this->failFast) {
					return false;
				}
			}
		}

		if (isset($schema['min_count'])) {
			if (count($node) < $schema['min_count']) {
				$wrongCount = count($node);
				$wrongPath = $path === '/' ? $path : rtrim($path, '/');
				$errors[] = "Wrong minimum items count in '$wrongPath'; expected '$schema[min_count]'; got '{$wrongCount}'";
				$isValid = false;
				if ($this->failFast) {
					return false;
				}
			}
		}

		foreach ($node as $index => $value) {
			$stack[] = [$schema['item'], $value, "$path$index/"];
		}

		return $isValid;
	}


	private function validateAnyOf($node, $options, $path, & $errors)
	{
		$results = [];
		foreach ($options as $optionSchema) {
			$validator = new Validator($optionSchema, true);
			$result = $validator->validate($node);
			if ($result->isValid()) {
				return true;
			} else {
				$results[] = $result;
			}
		}

		$wrongPath = $path === '/' ? $path : rtrim($path, '/');
		$errors[] = "Wrong data type in '$wrongPath'; expected validity at least for one of sub-schemas:";
		foreach ($results as $resultI => $result) {
			$errors[] = "- $resultI: " . $result->getErrors()[0];
		}
		return false;
	}


	private function validateAllOf($node, $options, $path, & $errors)
	{
		$results = [];
		$validCount = 0;
		foreach ($options as $optionSchema) {
			$validator = new Validator($optionSchema, true);
			$results[] = $result = $validator->validate($node);
			$validCount += $result->isValid() ? 1 : 0;
		}

		if ($validCount === count($options)) {
			return true;
		}

		$wrongPath = $path === '/' ? $path : rtrim($path, '/');
		$errors[] = "Wrong data type in '$wrongPath'; expected validity for all sub-schemas; invalid for:";
		foreach ($results as $resultI => $result) {
			if (!$result->isValid()) {
				$errors[] = "- $resultI: " . $result->getErrors()[0];
			}
		}
		return false;
	}


	private function validateOneOf($node, $options, $path, & $errors)
	{
		$results = [];
		$validCount = 0;
		foreach ($options as $optionSchema) {
			$validator = new Validator($optionSchema, true);
			$results[] = $result = $validator->validate($node);
			$validCount += $result->isValid() ? 1 : 0;
		}

		if ($validCount === 1) {
			return true;

		} elseif ($validCount === 0) {
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$errors[] = "Wrong data type in '$wrongPath'; expected validity for just one sub-schema:";
			foreach ($results as $resultI => $result) {
				$errors[] = "- $resultI: " . $result->getErrors()[0];
			}
			return false;

		} else {
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$indexes = implode(', ', array_keys($results));
			$errors[] = "Wrong data type in '$wrongPath'; expected validity for only one sub-schema; valid for schemas number: $indexes.";
			return false;
		}
	}

	private function validateReference($node, $schemaName, $path, & $stack)
	{
		$isValid = true;
		if (!is_callable($this->referenceCallback)) {
			throw new ValidatorException("Missing referenced scheme loader when trying to load: '$schemaName'");
		}
		$referencedSchema = call_user_func($this->referenceCallback, $schemaName);
		if ($referencedSchema === null) {
			throw new ValidatorException("Can`t load referenced scheme: '$schemaName'");
		}
		$stack[] = [$referencedSchema, $node, $path];
		return $isValid;
	}
}
