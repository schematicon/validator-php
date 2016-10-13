<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/validator-php
 */

namespace Schematicon\Validator;


class Result
{
	/** @var array */
	private $errors;


	public function __construct(array $errors)
	{
		$this->errors = $errors;
	}


	/**
	 * @return string[]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}


	public function isValid(): bool
	{
		return count($this->errors) === 0;
	}
}
