Schematicon Validator (PHP)
===========================

[![Build Status](https://travis-ci.org/schematicon/validator-php.svg?branch=master)](https://travis-ci.org/schematicon/validator-php)
[![Downloads this Month](https://img.shields.io/packagist/dm/schematicon/validator.svg?style=flat)](https://packagist.org/packages/schematicon/validator)
[![Stable version](http://img.shields.io/packagist/v/schematicon/validator.svg?style=flat)](https://packagist.org/packages/schematicon/validator)

**Validator** is [Schematicon Schema](https://github.com/schematicon/spec) validator. Schemeaticon schema is innovative declarative language for data structure description. It is programming-language independent; that means you can define the schema using [NEON](https://ne-on.org/), YAML od native PHP arrays.

### Example

`my_family.neon`:
```yaml
type: map
propeties:
	name: string
	surname: string
	sex:
		enum: [male, female]
	age: int|null # property may be a null
	?height: float # property may not exist at all; if exist, it has to be a float
	siblings:
		type: array
		item:
			type: string
```

The following inputs may be validated againts the defined schema:
```php
$normalizer = new Schematicon\Validator\Normalizer();
$schema = Neon\Neon::decode(file_get_contents('./my_family.neon'));
$schema = $normalizer->normalize($schema);
$validator = new Schematicon\Validator\Validator($schema);

$result = $validator->validate([
	'name' => 'jon',
	'surname' => 'snow',
	'sex' => 'male',
	'age' => 18,
	'height' => 180.00,
	'siblings' => ['Arya'],
];

$result->isValid(); // true
$result->getErrors(); // []
```

### Installation

Use composer:

```bash
$ composer require schematicon/validator
```

### License

MIT. See full [license](license.md).

The development was sponsored by [Sygic Travel](https://travel.sygic.com).
