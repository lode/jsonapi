<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\enums\JsonapiVersionEnum;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\HasMetaInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\AbstractObject;
use alsvanzelf\jsonapi\objects\MetaObject;

class JsonapiObject extends AbstractObject implements HasMetaInterface {
	/** @var JsonapiVersionEnum */
	protected $version;
	/** @var ExtensionInterface[] */
	protected $extensions = [];
	/** @var ProfileInterface[] */
	protected $profiles = [];
	/** @var MetaObject */
	protected $meta;
	
	public function __construct(?JsonapiVersionEnum $version=JsonapiVersionEnum::Latest) {
		if ($version !== null) {
			$this->setVersion($version);
		}
	}
	
	/**
	 * human api
	 */
	
	public function addMeta(string $key, mixed $value): void {
		if ($this->meta === null) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * spec api
	 */
	
	public function setVersion(JsonapiVersionEnum $version) {
		$this->version = $version;
	}
	
	/**
	 * @param ExtensionInterface $extension
	 */
	public function addExtension(ExtensionInterface $extension) {
		$this->extensions[] = $extension;
	}
	
	/**
	 * @param ProfileInterface $profile
	 */
	public function addProfile(ProfileInterface $profile) {
		$this->profiles[] = $profile;
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if ($this->version !== null) {
			return false;
		}
		if ($this->extensions !== []) {
			return false;
		}
		if ($this->profiles !== []) {
			return false;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			return false;
		}
		if ($this->hasAtMembers()) {
			return false;
		}
		if ($this->hasExtensionMembers()) {
			return false;
		}
		
		return true;
	}
	
	public function toArray(): array {
		$array = [];
		
		if ($this->hasAtMembers()) {
			$array = array_merge($array, $this->getAtMembers());
		}
		if ($this->hasExtensionMembers()) {
			$array = array_merge($array, $this->getExtensionMembers());
		}
		if ($this->version !== null) {
			$array['version'] = $this->version->value;
		}
		if ($this->extensions !== []) {
			$array['ext'] = [];
			foreach ($this->extensions as $extension) {
				$array['ext'][] = $extension->getOfficialLink();
			}
		}
		if ($this->profiles !== []) {
			$array['profile'] = [];
			foreach ($this->profiles as $profile) {
				$array['profile'][] = $profile->getOfficialLink();
			}
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
