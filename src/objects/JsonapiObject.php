<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\ExtensionMemberManager;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\MetaObject;

class JsonapiObject implements ObjectInterface {
	use AtMemberManager, ExtensionMemberManager;
	
	/** @var string */
	protected $version;
	/** @var ExtensionInterface[] */
	protected $extensions = [];
	/** @var ProfileInterface */
	protected $profiles = [];
	/** @var MetaObject */
	protected $meta;
	
	/**
	 * @param string $version one of the Document::JSONAPI_VERSION_* constants, optional, defaults to Document::JSONAPI_VERSION_LATEST
	 */
	public function __construct($version=Document::JSONAPI_VERSION_LATEST) {
		if ($version !== null) {
			$this->setVersion($version);
		}
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function addMeta($key, $value) {
		if ($this->meta === null) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $version
	 */
	public function setVersion($version) {
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
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
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
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		if ($this->hasAtMembers()) {
			$array = array_merge($array, $this->getAtMembers());
		}
		if ($this->hasExtensionMembers()) {
			$array = array_merge($array, $this->getExtensionMembers());
		}
		if ($this->version !== null) {
			$array['version'] = $this->version;
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
