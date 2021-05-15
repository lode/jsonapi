<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\ExtensionMemberManager;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\MetaObject;

class ResourceIdentifierObject implements ObjectInterface, ResourceInterface {
	use AtMemberManager, ExtensionMemberManager;
	
	/** @var string */
	protected $type;
	/** @var string */
	protected $id;
	/** @var string */
	protected $lid;
	/** @var MetaObject */
	protected $meta;
	/** @var Validator */
	protected $validator;
	
	/**
	 * @note $type and $id are optional to pass during construction
	 *       however they are required for a valid ResourceIdentifierObject
	 *       so use ->setType() and ->setId() if not passing them during construction
	 * 
	 * @param string     $type optional
	 * @param string|int $id   optional
	 */
	public function __construct($type=null, $id=null) {
		$this->validator = new Validator();
		
		if ($type !== null) {
			$this->setType($type);
		}
		if ($id !== null) {
			$this->setId($id);
		}
		
		// always mark as used, as these keys are reserved
		$this->validator->claimUsedFields($fieldNames=['type'], Validator::OBJECT_CONTAINER_TYPE);
		$this->validator->claimUsedFields($fieldNames=['id'], Validator::OBJECT_CONTAINER_ID);
		$this->validator->claimUsedFields($fieldNames=['lid'], Validator::OBJECT_CONTAINER_LID);
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
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	/**
	 * @param string|int $id will be casted to a string
	 * 
	 * @throws DuplicateException if localId is already set
	 */
	public function setId($id) {
		if ($this->lid !== null) {
			throw new DuplicateException('id is not allowed when localId is already set');
		}
		
		$this->id = (string) $id;
	}
	
	/**
	 * set a local id to connect resources to each other when created on the client
	 * 
	 * @note this should not be used to send back from the server to the client
	 * 
	 * @param string|int $localId will be casted to a string
	 * 
	 * @throws DuplicateException if normal id is already set
	 */
	public function setLocalId($localId) {
		if ($this->id !== null) {
			throw new DuplicateException('localId is not allowed when id is already set');
		}
		
		$this->lid = (string) $localId;
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @param  ResourceObject $resourceObject
	 * @return ResourceIdentifierObject
	 */
	public static function fromResourceObject(ResourceObject $resourceObject) {
		$resourceIdentifierObject = new self($resourceObject->type, $resourceObject->primaryId());
		
		if ($resourceObject->meta !== null) {
			$resourceIdentifierObject->setMetaObject($resourceObject->meta);
		}
		
		return $resourceIdentifierObject;
	}
	
	/**
	 * @internal
	 * 
	 * @param  ResourceInterface $resource
	 * @return boolean
	 * 
	 * @throws Exception if one or both are missing identification
	 */
	public function equals(ResourceInterface $resource) {
		if ($this->hasIdentification() === false || $resource->getResource()->hasIdentification() === false) {
			throw new Exception('can not compare resources if identification is missing');
		}
		
		return ($this->getIdentificationKey() === $resource->getResource()->getIdentificationKey());
	}
	
	/**
	 * @internal
	 * 
	 * @return boolean
	 */
	public function hasIdentification() {
		return ($this->type !== null && $this->primaryId() !== null);
	}
	
	/**
	 * get a key to uniquely define this resource
	 * 
	 * @internal
	 * 
	 * @return string
	 * 
	 * @throws Exception if type or id is not set yet
	 */
	public function getIdentificationKey() {
		if ($this->hasIdentification() === false) {
			throw new Exception('resource has no identification yet');
		}
		
		return $this->type.'|'.$this->primaryId();
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		if ($this->type !== null || $this->primaryId() !== null) {
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
		
		$array['type'] = $this->type;
		
		if ($this->id !== null) {
			$array['id'] = $this->id;
		}
		elseif ($this->lid !== null) {
			$array['lid'] = $this->lid;
		}
		
		if ($this->hasAtMembers()) {
			$array = array_merge($array, $this->getAtMembers());
		}
		if ($this->hasExtensionMembers()) {
			$array = array_merge($array, $this->getExtensionMembers());
		}
		
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * ResourceInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function getResource($identifierOnly=false) {
		return $this;
	}
	
	/**
	 * @internal
	 */
	
	private function primaryId() {
		if ($this->lid !== null) {
			return $this->lid;
		}
		
		return $this->id;
	}
}
