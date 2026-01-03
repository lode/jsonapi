<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use alsvanzelf\jsonapi\interfaces\DocumentInterface;

/**
 * @group OutputOnly
 */
class ExampleOutputTest extends TestCase {
	/** @var PHPStanTypeAlias_Options_Document */
	private static array $defaults = [
		'prettyPrint' => true,
	];
	
	#[DataProvider('dataProviderTestOutput')]
	public function testOutput(object $generator, ?string $expectedJson, ?string $testName): void {
		/** @var DocumentInterface $document */
		$document   = $generator::createJsonapiDocument(); // @phpstan-ignore staticMethod.notFound
		$actualJson = $document->toJson(self::$defaults);
		
		// adhere to editorconfig
		$actualJson = str_replace('    ', "\t", $actualJson).PHP_EOL;
		
		// create new cases
		$actualJsonPath = __DIR__.'/example_output/'.$testName.'/'.$testName.'.json';
		if ($expectedJson === null && file_exists($actualJsonPath) === false) {
			file_put_contents($actualJsonPath, $actualJson);
			parent::markTestSkipped('no stored json to test against, try again');
		}
		
		parent::assertSame($expectedJson, $actualJson);
	}
	
	/**
	 * @return array<string, array{
	 *         0: object,
	 *         1: ?string,
	 *         2: string
	 * }>
	 */
	public static function dataProviderTestOutput(): array {
		$directories = glob(__DIR__.'/example_output/*', GLOB_ONLYDIR);
		if ($directories === false) {
			throw new \Exception('failed to fetch example output');
		}
		
		$testCases = [];
		foreach ($directories as $directory) {
			$testName  = basename($directory);
			$className = '\\alsvanzelf\\jsonapiTests\\example_output\\'.$testName.'\\'.$testName;
			
			require $directory.'/'.$testName.'.php';
			
			$generator    = new $className;
			$expectedJson = null;
			
			if (file_exists($directory.'/'.$testName.'.json')) {
				$expectedJson = file_get_contents($directory.'/'.$testName.'.json');
				if ($expectedJson === false) {
					throw new \Exception('something went wrong fetching expected output');
				}
			}
			
			$testCases[$testName] = [$generator, $expectedJson, $testName];
		}
		
		return $testCases;
	}
}
