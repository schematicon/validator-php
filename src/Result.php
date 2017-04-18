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

	/** @var mixed */
	private $data;


	public function __construct(array $errors, $data)
	{
		$this->errors = $errors;
		$this->data = $data;
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


	public function getData()
	{
		return $this->data;
	}
}
