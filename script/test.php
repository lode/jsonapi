<?php

$extraParameters = '';
if (isset($argv[1])) {
	$extraParameters = $argv;
	unset($extraParameters[0]);
	$extraParameters = ' '.implode(' ', $extraParameters);
}

if (PHP_MAJOR_VERSION < 7 && strpos($extraParameters, 'non-php5') === false) {
	$extraParameters .= ' --exclude-group=non-php5';
}

passthru(realpath(__DIR__.'/../vendor/bin/phpunit').' --colors=always'.$extraParameters, $returnCode);

exit($returnCode);
