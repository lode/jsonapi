<?php

$extraParameters = '';
if (isset($argv[1])) {
	$extraParameters = $argv;
	unset($extraParameters[0]);
	$extraParameters = ' '.implode(' ', $extraParameters);
}

passthru(realpath(__DIR__.'/../vendor/bin/phpunit').' --colors=always'.$extraParameters, $returnCode);

exit($returnCode);
