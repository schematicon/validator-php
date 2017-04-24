<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/validator-php
 */

namespace Schematicon\Validator;


class Validator
{
	/** @var bool */
	public $coerceStringToInt = false;

	/** @var bool */
	public $coerceStringToFloat = false;

	/** @var bool */
	public $coerceStringToBool = false;

	/** @var array */
	private $schema;

	/** @var bool */
	private $failFast;

	/** @var callable */
	private $referenceCallback;


	public function __construct(array $schema, bool $failFast = false, callable $referenceCallback = null)
	{
		$this->schema = $schema;
		$this->failFast = $failFast;
		$this->referenceCallback = $referenceCallback;
	}


	public function validate($data): Result
	{
		$outData = $data;
		$errors = [];
		$stack = [[$this->schema, & $outData, '/']];
		while ($row = array_pop($stack)) {
			$schema = $row[0];
			$node = & $row[1];
			$path = $row[2];

			if (isset($schema['reference']) && is_string($schema['reference'])) {
				$isValid = $this->validateReference($node, $schema['reference'], $path, $stack);

			} elseif (isset($schema['anyOf'])) {
				$isValid = $this->validateAnyOf($node, $schema['anyOf'], $path, $errors);

			} elseif (isset($schema['oneOf'])) {
				$isValid = $this->validateOneOf($node, $schema['oneOf'], $path, $errors);

			} elseif (isset($schema['allOf'])) {
				$isValid = $this->validateAllOf($node, $schema['allOf'], $path, $errors);

			} elseif (isset($schema['enum'])) {
				$isValid = in_array($node, $schema['enum'], true);
				if (!$isValid) {
					$wrongPath = $path === '/' ? $path : rtrim($path, '/');
					$enum = substr(json_encode($schema['enum']), 1, -1);
					$wrongType = Helpers::getVariableType($node);
					array_unshift($errors, "Wrong value in '$wrongPath'; expected value from [$enum]; got type '$wrongType'");
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
							$isValid = $this->validateString($node, $schema, $path, $errors);
							break;
						}
					} elseif ($type === 'bool') {
						if (is_bool($node)) {
							$isValid = true;
							break;
						} elseif ($this->coerceStringToBool && ($node === '1' || $node === '0')) {
							$node = (bool) $node;
							$isValid = true;
							break;
						}
					} elseif ($type === 'int') {
						if (is_int($node)) {
							$isValid = $this->validateNumber($node, $schema, $path, $errors);
							break;
						} elseif ($this->coerceStringToInt && is_string($node) && ($filteredValue = filter_var($node, FILTER_VALIDATE_INT)) !== false) {
							$node = $filteredValue;
							$isValid = $this->validateNumber($node, $schema, $path, $errors);
							break;
						}
					} elseif ($type === 'float') {
						if (is_float($node)) {
							$isValid = $this->validateNumber($node, $schema, $path, $errors);
							break;
						} elseif ($this->coerceStringToFloat && is_string($node) && ($filteredValue = filter_var($node, FILTER_VALIDATE_FLOAT)) !== false) {
							$node = $filteredValue;
							$isValid = $this->validateNumber($node, $schema, $path, $errors);
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

		return new Result($errors, $outData);
	}


	private function validateInnerProperties(& $node, array $schema, string $path, array & $stack, array & $errors): bool
	{
		$isValid = true;
		if (is_array($node)) {
			foreach ($schema['properties'] ?? [] as $propName => $propSchema) {
				if (isset($node[$propName]) || array_key_exists($propName, $node)) {
					$stack[] = [$propSchema, & $node[$propName], "$path$propName/"];

				} elseif (!isset($propSchema['optional']) || !$propSchema['optional']) {
					$errors[] = "Missing '$propName' key in '$path' path";
					$isValid = false;
					if ($this->failFast) {
						break;
					}
				}
			}
		} else {
			// stdClass
			foreach ($schema['properties'] ?? [] as $propName => $propSchema) {
				if (isset($node->$propName) || property_exists($node, $propName)) {
					$stack[] = [$propSchema, & $node->$propName, "$path$propName/"];

				} elseif (!isset($propSchema['optional']) || !$propSchema['optional']) {
					$errors[] = "Missing '$propName' key in '$path' path";
					$isValid = false;
					if ($this->failFast) {
						break;
					}
				}
			}
		}

		foreach ($schema['regexpProperties'] ?? [] as $propName => $propSchema) {
			$expression = "~$propName~";
			foreach ($node as $nodeKey => & $nodeValue) {
				if (preg_match($expression, $nodeKey) !== 1) {
					continue;
				}

				$stack[] = [$propSchema, & $nodeValue, "$path$propName/"];
			}
		}

		return $isValid;
	}


	private function validateItems(& $node, array $schema, string $path, array & $stack, array & $errors): bool
	{
		$isValid = true;

		if (isset($schema['maxCount'])) {
			if (count($node) > $schema['maxCount']) {
				$wrongCount = count($node);
				$wrongPath = $path === '/' ? $path : rtrim($path, '/');
				$errors[] = "Wrong maximum items count in '$wrongPath'; expected '$schema[maxCount]'; got '{$wrongCount}'";
				$isValid = false;
				if ($this->failFast) {
					return false;
				}
			}
		}

		if (isset($schema['minCount'])) {
			if (count($node) < $schema['minCount']) {
				$wrongCount = count($node);
				$wrongPath = $path === '/' ? $path : rtrim($path, '/');
				$errors[] = "Wrong minimum items count in '$wrongPath'; expected '$schema[minCount]'; got '{$wrongCount}'";
				$isValid = false;
				if ($this->failFast) {
					return false;
				}
			}
		}

		foreach ($node as $index => & $value) {
			$stack[] = [$schema['item'], & $value, "$path$index/"];
		}

		return $isValid;
	}


	private function validateAnyOf($node, array $options, string $path, array & $errors): bool
	{
		$results = [];
		foreach ($options as $optionSchema) {
			$validator = new Validator($optionSchema, true, $this->referenceCallback);
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


	private function validateAllOf($node, array $options, string $path, array & $errors): bool
	{
		$results = [];
		$validCount = 0;
		foreach ($options as $optionSchema) {
			$validator = new Validator($optionSchema, true, $this->referenceCallback);
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


	private function validateOneOf($node, array $options, string $path, array & $errors): bool
	{
		$results = [];
		$validCount = 0;
		foreach ($options as $optionSchema) {
			$validator = new Validator($optionSchema, true, $this->referenceCallback);
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


	private function validateReference($node, string $schemaName, string $path, array & $stack): bool
	{
		$isValid = true;
		if (!is_callable($this->referenceCallback)) {
			throw new ValidatorException("Missing referenced scheme loader when trying to load: '$schemaName'");
		}
		$referencedSchema = call_user_func($this->referenceCallback, $schemaName);
		if ($referencedSchema === null) {
			throw new SchemaNotFound("Reference schema loader cannot load schema with '$schemaName' name.");
		}
		$stack[] = [$referencedSchema, $node, $path];
		return $isValid;
	}


	private function validateString($node, array $schema, string $path, array & $errors): bool
	{
		$isValid = true;
		if (isset($schema['regexp']) && preg_match($schema['regexp'], $node) !== 1) {
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$wrongType = Helpers::getVariableType($node);
			$errors[] = "Wrong value in '$wrongPath'; expected value matching '$schema[regexp]' regexp; got type '$wrongType'";
			$isValid = false;
			if ($this->failFast) {
				return $isValid;
			}
		}

		if (isset($schema['minLength']) && mb_strlen($node) < $schema['minLength']) {
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$wrongLength = mb_strlen($node);
			$errors[] = "Wrong value in '$wrongPath'; expected string of minimal length '$schema[minLength]'; got length '$wrongLength'";
			$isValid = false;
			if ($this->failFast) {
				return $isValid;
			}
		} elseif (isset($schema['maxLength']) && mb_strlen($node) > $schema['maxLength']) {
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$wrongLength = mb_strlen($node);
			$errors[] = "Wrong value in '$wrongPath'; expected string of maximal length '$schema[maxLength]'; got length '$wrongLength'";
			$isValid = false;
			if ($this->failFast) {
				return $isValid;
			}
		}

		return $isValid;
	}


	private function validateNumber($node, array $schema, string $path, array & $errors): bool
	{
		$isValid = true;

		if (isset($schema['minValue']) && $node < $schema['minValue']) {
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$errors[] = "Wrong value in '$wrongPath'; expected number of minimal value '$schema[minValue]'; got value '$node'";
			$isValid = false;
			if ($this->failFast) {
				return $isValid;
			}
		} elseif (isset($schema['maxValue']) && $node > $schema['maxValue']) {
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$errors[] = "Wrong value in '$wrongPath'; expected number of maximal value '$schema[maxValue]'; got value '$node'";
			$isValid = false;
			if ($this->failFast) {
				return $isValid;
			}
		}

		return $isValid;
	}
}
