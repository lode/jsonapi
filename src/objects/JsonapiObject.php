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
	protected JsonapiVersionEnum $version;
	/** @var ExtensionInterface[] */
	protected array $extensions = [];
	/** @var ProfileInterface[] */
	protected array $profiles = [];
	protected MetaObject $meta;
	
	public function __construct(?JsonapiVersionEnum $version=JsonapiVersionEnum::Latest) {
		if ($version !== null) {
			$this->setVersion($version);
		}
	}
	
	/**
	 * human api
	 */
	
	public function addMeta(string $key, mixed $value): void {
		if (isset($this->meta) === false) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * spec api
	 */
	
	public function setVersion(JsonapiVersionEnum $version): void {
		$this->version = $version;
	}
	
	public function addExtension(ExtensionInterface $extension): void {
		$this->extensions[] = $extension;
	}
	
	public function addProfile(ProfileInterface $profile): void {
		$this->profiles[] = $profile;
	}
	
	public function setMetaObject(MetaObject $metaObject): void {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if (isset($this->version)) {
			return false;
		}
		if ($this->extensions !== []) {
			return false;
		}
		if ($this->profiles !== []) {
			return false;
		}
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
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
			$array = [...$array, ...$this->getAtMembers()];
		}
		if ($this->hasExtensionMembers()) {
			$array = [...$array, ...$this->getExtensionMembers()];
		}
		if (isset($this->version)) {
			if ($this->version === JsonapiVersionEnum::Latest) {
				$this->version = JsonapiVersionEnum::latest();
			}
			
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
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
