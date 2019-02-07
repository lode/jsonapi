<?php

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\TestCase;

class ExampleOutputTest extends TestCase {
	private static $defaults = [
		'prettyPrint' => true,
	];
	
	/**
	 * @dataProvider dataProviderTestOutput
	 */
	public function testOutput($generator, $expectedJson, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		$document   = $generator::createJsonapiDocument();
		$actualJson = $document->toJson($options);
		
		// adhere to editorconfig
		$actualJson = str_replace('    ', "\t", $actualJson).PHP_EOL;
		
		$this->assertSame($expectedJson, $actualJson);
	}
	
	public function dataProviderTestOutput() {
		$directories = glob(__DIR__.'/example_output/*', GLOB_ONLYDIR);
		
		$testCases = [];
		foreach ($directories as $directory) {
			$testName  = basename($directory);
			$className = '\\alsvanzelf\\jsonapiTests\\example_output\\'.$testName.'\\'.$testName;
			
			require $directory.'/'.$testName.'.php';
			
			$generator    = new $className;
			$expectedJson = null;
			$options      = [];
			
			if (file_exists($directory.'/'.$testName.'.json')) {
				$expectedJson = file_get_contents($directory.'/'.$testName.'.json');
			}
			if (file_exists($directory.'/options.txt')) {
				$options = json_decode(file_get_contents($directory.'/options.txt'), true);
			}
			
			$testCases[$testName] = [$generator, $expectedJson, $options];
		}
		
		return $testCases;
	}
}
