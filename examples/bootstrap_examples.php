<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

ini_set('display_errors', 1);
error_reporting(-1);

require_once __DIR__.'/../vendor/autoload.php';

class ExampleDataset {
	private static $records = [
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
	
	public static function getRecord($type, $id) {
		if (!isset(self::$records[$type][$id])) {
			throw new Exception('sorry, we have a limited dataset');
		}
		
		return self::$records[$type][$id];
	}
	
	public static function getEntity($type, $id) {
		$record = self::getRecord($type, $id);
		
		$user = new ExampleUser($id);
		foreach ($record as $key => $value) {
			$user->$key = $value;
		}
		
		return $user;
	}
	
	public static function findRecords($type) {
		return self::$records[$type];
	}
	
	public static function findEntities($type) {
		$records  = self::findRecords($type);
		$entities = [];
		
		foreach ($records as $id => $record) {
			$entities[$id] = self::getEntity($type, $id);
		}
		
		return $entities;
	}
}

class ExampleUser {
	public $id;
	public $name;
	public $heads;
	public $unknown;
	
	public function __construct($id) {
		$this->id = $id;
	}
	
	function getCurrentLocation() {
		return 'Earth';
	}
}

class ExampleVersionExtension implements ExtensionInterface {
	/**
	 * the required method
	 */
	
	public function getOfficialLink() {
		return 'https://jsonapi.org/format/1.1/#extension-rules';
	}
	
	public function getNamespace() {
		return 'version';
	}
	
	/**
	 * optionally helpers for the specific extension
	 */
	
	public function setVersion(ResourceInterface $resource, $version) {
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
	
	public function getOfficialLink() {
		return 'https://jsonapi.org/recommendations/#authoring-profiles';
	}
	
	/**
	 * optionally helpers for the specific profile
	 */
	
	public function setTimestamps(ResourceInterface $resource, \DateTimeInterface $created=null, \DateTimeInterface $updated=null) {
		if ($resource instanceof ResourceIdentifierObject) {
			throw new Exception('cannot add attributes to identifier objects');
		}
		
		$timestamps = [];
		if ($created !== null) {
			$timestamps['created'] = $created->format(\DateTimeInterface::ISO8601);
		}
		if ($updated !== null) {
			$timestamps['updated'] = $updated->format(\DateTimeInterface::ISO8601);
		}
		
		$resource->add('timestamps', $timestamps);
	}
}
