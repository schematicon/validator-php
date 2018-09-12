<?php

namespace SchematiconTests;

use Nette\Neon\Neon;
use Schematicon\Validator\Validator;
use Tester\Assert;


require_once __DIR__ . '/../bootstrap.php';

$config = Neon::decode(<<<NEON
basic:
	type: url
nullable:
	type: url|null
NEON
);

$validator = new Validator(prepareSchema($config['basic']));

Assert::true($validator->validate('https://domain.com')->isValid());
Assert::true($validator->validate('http://domain.com')->isValid());
Assert::true($validator->validate('https://subdomain.domain.com')->isValid());
Assert::true($validator->validate('https://subdomain.domain.com/somepath')->isValid());
Assert::true($validator->validate('https://subdomain.domain.com/somepath/test')->isValid());
Assert::true($validator->validate('https://subdomain.domain.com/somepath/test')->isValid());
Assert::true($validator->validate('https://subsub.subdomain.domain.com/test')->isValid());
Assert::true($validator->validate('https://domain.com?param=test')->isValid());
Assert::true($validator->validate('https://domain.com?param=test&amp;user=test')->isValid());
Assert::true($validator->validate('https://domain.com?param=test&user=test')->isValid());

Assert::same(
	["Wrong data type in '/'; expected 'url'; got 'float'"],
	$validator->validate(1.0)->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'url'; got 'array'"],
	$validator->validate([])->getErrors()
);

Assert::same(
	["Wrong data type in '/'; expected 'url'; got 'bool'"],
	$validator->validate(true)->getErrors()
);

Assert::same(
	["Wrong value in '/'; expected valid URL (RFC 2396)."],
	$validator->validate('plainurl')->getErrors()
);
Assert::same(
	["Wrong value in '/'; expected valid URL (RFC 2396)."],
	$validator->validate('domain.com')->getErrors()
);
Assert::same(
	["Wrong value in '/'; expected valid URL (RFC 2396)."],
	$validator->validate('http:///domain.com')->getErrors()
);
Assert::same(
	["Wrong value in '/'; expected valid URL (RFC 2396)."],
	$validator->validate('http::/domain.com')->getErrors()
);
Assert::same(
	["Wrong value in '/'; expected valid URL (RFC 2396)."],
	$validator->validate('http::/domain.com&param=test')->getErrors()
);

Assert::type('string', $validator->validate('https://domain.com')->getData());

// =====================================================================================================================

$validator = new Validator(prepareSchema($config['nullable']));

Assert::true($validator->validate(null)->isValid());

Assert::true($validator->validate('http://domain.com')->isValid());

Assert::same(
	["Wrong data type in '/'; expected 'url|null'; got 'array'"],
	$validator->validate([])->getErrors()
);


// =====================================================================================================================

