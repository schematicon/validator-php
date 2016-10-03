<?php

namespace NextrasTests\Schematicon;

use Tester\Environment;

require_once __DIR__ . '/../vendor/autoload.php';
Environment::setup();

header('Content-type: text/plain');
putenv('ANSICON=TRUE');
