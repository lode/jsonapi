<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\enums\ObjectContainerEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\HasMetaInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\AbstractObject;
use alsvanzelf\jsonapi\objects\MetaObject;

class ResourceIdentifierObject extends AbstractObject implements HasMetaInterface, ResourceInterface {
	protected string $type;
	protected string $id;
	protected string $lid;
	protected MetaObject $meta;
	protected readonly Validator $validator;
	
	/**
	 * @note $type and $id are optional to pass during construction
	 *       however they are required for a valid ResourceIdentifierObject
	 *       so use ->setType() and ->setId() if not passing them during construction
	 */
	public function __construct(?string $type=null, string|int|null $id=null) {
		$this->validator = new Validator();
		
		if ($type !== null) {
			$this->setType($type);
		}
		if ($id !== null) {
			$this->setId($id);
		}
		
		// always mark as used, as these keys are reserved
		$this->validator->claimUsedFields($fieldNames=['type'], ObjectContainerEnum::Type);
		$this->validator->claimUsedFields($fieldNames=['id'], ObjectContainerEnum::Id);
		$this->validator->claimUsedFields($fieldNames=['lid'], ObjectContainerEnum::Lid);
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
	
	public function setType(string $type): void {
		$this->type = $type;
	}
	
	/**
	 * int $id will be casted to a string
	 * 
	 * @throws DuplicateException if localId is already set
	 */
	public function setId(string|int $id): void {
		if (isset($this->lid)) {
			throw new DuplicateException('id is not allowed when localId is already set');
		}
		
		$this->id = (string) $id;
	}
	
	/**
	 * set a local id to connect resources to each other when created on the client
	 * 
	 * @note this should not be used to send back from the server to the client
	 * 
	 * int $localId will be casted to a string
	 * 
	 * @throws DuplicateException if normal id is already set
	 */
	public function setLocalId(string|int $localId): void {
		if (isset($this->id)) {
			throw new DuplicateException('localId is not allowed when id is already set');
		}
		
		$this->lid = (string) $localId;
	}
	
	public function setMetaObject(MetaObject $metaObject): void {
		$this->meta = $metaObject;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @throws InputException if the $resourceoObject's type or id is not set yet
	 */
	public static function fromResourceObject(ResourceObject $resourceObject): self {
		if ($resourceObject->hasIdentification() === false) {
			throw new InputException('resource has no identification yet<');
		}
		
		$resourceIdentifierObject = new self($resourceObject->type, $resourceObject->primaryId());
		
		if (isset($resourceObject->meta)) {
			$resourceIdentifierObject->setMetaObject($resourceObject->meta);
		}
		
		return $resourceIdentifierObject;
	}
	
	/**
	 * @internal
	 * 
	 * @throws Exception if one or both are missing identification
	 */
	public function equals(ResourceInterface $resource): bool {
		if ($this->hasIdentification() === false || $resource->getResource()->hasIdentification() === false) {
			throw new Exception('can not compare resources if identification is missing');
		}
		
		return ($this->getIdentificationKey() === $resource->getResource()->getIdentificationKey());
	}
	
	/**
	 * @internal
	 */
	public function hasIdentification(): bool {
		if (isset($this->type) === false) {
			return false;
		}
		
		if (isset($this->id) === false && isset($this->lid) === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * get a key to uniquely define this resource
	 * 
	 * @internal
	 * 
	 * @throws Exception if type or id is not set yet
	 */
	public function getIdentificationKey(): string {
		if ($this->hasIdentification() === false) {
			throw new Exception('resource has no identification yet');
		}
		
		return $this->type.'|'.$this->primaryId();
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if (isset($this->type)) {
			return false;
		}
		if (isset($this->id) || isset($this->lid)) {
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
		
		if (isset($this->type)) {
			$array['type'] = $this->type;
		}
		if (isset($this->id)) {
			$array['id'] = $this->id;
		}
		elseif (isset($this->lid)) {
			$array['lid'] = $this->lid;
		}
		
		if ($this->hasAtMembers()) {
			$array = [...$array, ...$this->getAtMembers()];
		}
		if ($this->hasExtensionMembers()) {
			$array = [...$array, ...$this->getExtensionMembers()];
		}
		
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * ResourceInterface
	 */
	
	public function getResource(bool $identifierOnly=false): ResourceIdentifierObject {
		return $this;
	}
	
	/**
	 * @internal
	 */
	
	private function primaryId(): string {
		if (isset($this->id) === false && isset($this->lid) === false) {
			throw new Exception('resource has no identification yet');
		}
		
		if (isset($this->lid)) {
			return $this->lid;
		}
		
		return $this->id;
	}
}
