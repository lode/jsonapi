<?php

declare(strict_types=1);

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\HasAttributesInterface;
use alsvanzelf\jsonapi\interfaces\HasExtensionMembersInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

ini_set('display_errors', 1);
error_reporting(-1);

require_once __DIR__.'/../vendor/autoload.php';

class ExampleDataset {
	/** @var array<string, array<int, array<string, string|int>>> */
	private static array $records = [
		'articles' => [
			1 => [
				'title'    => 'JSON:API paints my bikeshed!',
				'authorId' => 9,
			],
		],
		'comments' => [
			5 => [
				'body'     => 'First!',
				'authorId' => 2,
			],
			12 => [
				'body'     => 'I like XML better',
				'authorId' => 9,
			],
		],
		'people' => [
			9 => [
				'firstName' => 'Dan',
				'lastName'  => 'Gebhardt',
				'twitter'   => 'dgeb',
			],
		],
		'user' => [
			1 => [
				'name'  => 'Ford Prefect',
				'heads' => 1,
			],
			2 => [
				'name' => 'Arthur Dent',
				'heads' => '1, but not always there',
			],
			42 => [
				'name'  => 'Zaphod Beeblebrox',
				'heads' => 2,
			],
		],
	];
	
	/**
	 * @return array<string, string|int>
	 */
	public static function getRecord(string $type, int $id): array {
		if (isset(self::$records[$type][$id]) === false) {
			throw new \Exception('sorry, we have a limited dataset');
		}
		
		return self::$records[$type][$id];
	}
	
	public static function getEntity(string $type, int $id): ExampleUser {
		$record = self::getRecord($type, $id);
		
		$user = new ExampleUser($id);
		foreach ($record as $key => $value) {
			$user->$key = $value;
		}
		
		return $user;
	}
	
	/**
	 * @return array<array<string, string|int>>
	 */
	public static function findRecords(string $type): array {
		return self::$records[$type];
	}
	
	/**
	 * @return ExampleUser[]
	 */
	public static function findEntities(string $type): array {
		$records  = self::findRecords($type);
		$entities = [];
		
		foreach ($records as $id => $record) {
			$entities[$id] = self::getEntity($type, $id);
		}
		
		return $entities;
	}
}

class ExampleUser {
	public string $name;
	public int|string $heads;
	public mixed $unknown;
	
	public function __construct(
		public int $id,
	) {}
	
	function getCurrentLocation(): string {
		return 'Earth';
	}
}

class ExampleVersionExtension implements ExtensionInterface {
	/**
	 * the required method
	 */
	
	public function getOfficialLink(): string {
		return 'https://jsonapi.org/format/1.1/#extension-rules';
	}
	
	public function getNamespace(): string {
		return 'version';
	}
	
	/**
	 * optionally helpers for the specific extension
	 */
	
	public function setVersion(ResourceInterface $resource, string $version): void {
		if ($resource instanceof HasExtensionMembersInterface === false) {
			throw new \Exception('resource doesn\'t have extension members');
		}
		
		if ($resource instanceof ResourceDocument) {
			$resource->getResource()->addExtensionMember($this, 'id', $version);
		}
		else {
			$resource->addExtensionMember($this, 'id', $version);
		}
	}
}

class ExampleTimestampsProfile implements ProfileInterface {
	/**
	 * the required method
	 */
	
	public function getOfficialLink(): string {
		return 'https://jsonapi.org/recommendations/#authoring-profiles';
	}
	
	/**
	 * optionally helpers for the specific profile
	 */
	
	public function setTimestamps(
		ResourceInterface & HasAttributesInterface $resource,
		?\DateTimeInterface $created=null,
		?\DateTimeInterface $updated=null,
	): void {
		$timestamps = [];
		if ($created !== null) {
			$timestamps['created'] = $created->format(\DateTime::ISO8601);
		}
		if ($updated !== null) {
			$timestamps['updated'] = $updated->format(\DateTime::ISO8601);
		}
		
		$resource->addAttribute('timestamps', $timestamps);
	}
}
