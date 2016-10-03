<?php

/**
 * This file is part of the Nextras\Schematicon library.
 * @license    MIT
 * @link       https://github.com/nextras/schematicon
 */

namespace Nextras\Schematicon;


final class Validator
{
	private $schema;

	/** @var bool */
	private $failFast;


	public function __construct($schema, bool $failFast = false)
	{
		$this->schema = $schema;
		$this->failFast = $failFast;
	}


	public function validate($data)
	{
		$errors = [];
		$stack = [[$this->schema, $data, '/']];
		while (list ($schema, $node, $path) = array_pop($stack)) {
			$isValid = null;
			$types = explode('|', $schema['type']);
			foreach ($types as $type) {
				if ($type === 'null') {
					if ($node === null) {
						$isValid = true;
						break;
					}
				} elseif ($type === 'string') {
					if (is_string($node)) {
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
				} elseif ($type === 'array') {
					if (is_array($node)) {
						$isValid = $this->validateItems($node, $schema, $path, $stack, $errors);
						break;
					}
				} elseif ($type === 'map') {
					if (is_array($node)) {
						$isValid = $this->validateInnerKeys($node, $schema, $path, $stack, $errors);
						break;
					}
				}
			}
			if ($isValid === null) {
				$wrongType = Helpers::getVariableType($node);
				$wrongPath = $path === '/' ? $path : rtrim($path, '/');
				$errors[] = "Wrong data type in '$wrongPath'; expected '$schema[type]'; got '{$wrongType}'";
				$isValid = false;
			}

			if ($isValid === false && $this->failFast) {
				break;
			}
		}

		return new Result($errors);
	}


	private function validateInnerKeys($node, $schema, $path, & $stack, & $errors)
	{
		$isValid = true;
		foreach ($schema['keys'] as $keyName => $keySchema) {
			if (isset($node[$keyName]) || array_key_exists($keyName, $node)) {
				$stack[] = [$keySchema, $node[$keyName], "$path$keyName/"];

			} elseif (!isset($keySchema['optional']) || !$keySchema['optional']) {
				$errors[] = "Missing key in '$path$keyName'";
				$isValid = false;
				if ($this->failFast) {
					break;
				}
			}
		}
		return $isValid;
	}


	private function validateItems($node, $schema, $path, & $stack, & $errors)
	{
		$isValid = true;

		if (!Helpers::isList($node)) {
			$wrongType = Helpers::getVariableType($node);
			$wrongPath = $path === '/' ? $path : rtrim($path, '/');
			$errors[] = "Wrong data type in '$wrongPath'; expected '$schema[type]'; got '{$wrongType}'";
			$isValid = false;
			if ($this->failFast) {
				return false;
			}
		}

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
}
