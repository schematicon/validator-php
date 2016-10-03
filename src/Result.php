<?php

/**
 * This file is part of the Nextras\Schematicon library.
 * @license    MIT
 * @link       https://github.com/nextras/schematicon
 */

namespace Nextras\Schematicon;


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
