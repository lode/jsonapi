<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\ExtensionMemberManager;
use alsvanzelf\jsonapi\helpers\HttpStatusCodeManager;
use alsvanzelf\jsonapi\helpers\LinksManager;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\DocumentInterface;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\HasExtensionMembersInterface;
use alsvanzelf\jsonapi\interfaces\HasLinksInterface;
use alsvanzelf\jsonapi\interfaces\HasMetaInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;

/**
 * @phpstan-consistent-constructor
 * warn when an extending constructor changes the arguments
 * that might break the class since we use `new static()`
 * 
 * @see ResourceDocument, CollectionDocument, ErrorsDocument or MetaDocument
 */
abstract class Document implements DocumentInterface, \JsonSerializable, HasLinksInterface, HasMetaInterface, HasExtensionMembersInterface {
	use AtMemberManager;
	use ExtensionMemberManager;
	use HttpStatusCodeManager;
	use LinksManager {
		LinksManager::addLink as linkManagerAddLink;
	}
	
	protected MetaObject $meta;
	protected ?JsonapiObject $jsonapi;
	/** @var ExtensionInterface[] */
	protected array $extensions = [];
	/** @var ProfileInterface[] */
	protected array $profiles = [];
	/** @var PHPStanTypeAlias_InternalOptions */
	protected static array $defaults = [
		/**
		 * encode to json with these default options
		 */
		'encodeOptions' => JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR,
		
		/**
		 * encode to human-readable json, useful when debugging
		 */
		'prettyPrint' => false,
		
		/**
		 * send out the official jsonapi content-type header
		 * overwrite for jsonp or if clients don't support it
		 */
		'contentType' => ContentTypeEnum::Official,
		
		/**
		 * overwrite the array to encode to json
		 */
		'array' => null,
		
		/**
		 * overwrite the json to send as response
		 */
		'json' => null,
		
		/**
		 * set the callback for jsonp responses
		 */
		'jsonpCallback' => null,
	];
	
	public function __construct() {
		$this->setHttpStatusCode(200);
		$this->setJsonapiObject(new JsonapiObject());
	}
	
	/**
	 * human api
	 */
	
	/**
	 * if $meta is given, a LinkObject is added, otherwise a link string is added
	 * 
	 * @param array<string, mixed> $meta
	 * 
	 * @throws InputException if the $level is not DocumentLevelEnum::Root
	 */
	public function addLink(string $key, ?string $href, array $meta=[], DocumentLevelEnum $level=DocumentLevelEnum::Root): void {
		match ($level) {
			DocumentLevelEnum::Root     => $this->linkManagerAddLink($key, $href, $meta),
			DocumentLevelEnum::Jsonapi  => throw new InputException('level "jsonapi" can not be used for links'),
			DocumentLevelEnum::Resource => throw new InputException('level "resource" can only be set on a ResourceDocument'),
		};
	}
	
	/**
	 * set the self link on the document
	 * 
	 * @note a LinkObject is added when extensions or profiles are applied
	 * 
	 * @param array<string, mixed> $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function setSelfLink(string $href, array $meta=[], DocumentLevelEnum $level=DocumentLevelEnum::Root): void {
		if ($level === DocumentLevelEnum::Root && ($this->extensions !== [] || $this->profiles !== [])) {
			$contentType = Converter::prepareContentType(ContentTypeEnum::Official, $this->extensions, $this->profiles);
			
			$linkObject = new LinkObject($href, $meta);
			$linkObject->setMediaType($contentType);
			
			$this->addLinkObject('self', $linkObject);
		}
		else {
			$this->addLink('self', $href, $meta, $level);
		}
	}
	
	/**
	 * set a link describing the current document
	 * 
	 * for example this could link to an OpenAPI or JSON Schema document
	 * 
	 * @note according to the spec, this can only be set to DocumentLevelEnum::Root
	 * 
	 * @param array<string, mixed> $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function setDescribedByLink(string $href, array $meta=[]): void {
		$this->addLink('describedby', $href, $meta, DocumentLevelEnum::Root);
	}
	
	/**
	 * @throws InputException if the $level is DocumentLevelEnum::Resource
	 */
	public function addMeta(string $key, mixed $value, DocumentLevelEnum $level=DocumentLevelEnum::Root): void {
		switch ($level) {
			case DocumentLevelEnum::Root:
				if (isset($this->meta) === false) {
					$this->setMetaObject(new MetaObject());
				}
				
				$this->meta->add($key, $value);
				break;
			
			case DocumentLevelEnum::Jsonapi:
				if (isset($this->jsonapi) === false) {
					$this->setJsonapiObject(new JsonapiObject());
				}
				
				$this->jsonapi->addMeta($key, $value);
				break;
			
			case DocumentLevelEnum::Resource:
				throw new InputException('level "resource" can only be set on a ResourceDocument');
		}
	}
	
	/**
	 * spec api
	 */
	
	public function setMetaObject(MetaObject $metaObject): void {
		$this->meta = $metaObject;
	}
	
	public function setJsonapiObject(JsonapiObject $jsonapiObject): void {
		$this->jsonapi = $jsonapiObject;
	}
	
	/**
	 * hide that this api supports jsonapi, or which version it is using
	 */
	public function unsetJsonapiObject(): void {
		$this->jsonapi = null;
	}
	
	/**
	 * apply a extension which adds the link and sets a correct content-type
	 * 
	 * note that the rules from the extension are not automatically enforced
	 * applying the rules, and applying them correctly, is manual
	 * however the $extension could have custom methods to help
	 * 
	 * @see https://jsonapi.org/extensions/#extensions
	 * 
	 * @throws Exception if namespace uses illegal characters
	 * @throws DuplicateException if namespace conflicts with another applied extension
	 */
	public function applyExtension(ExtensionInterface $extension): void {
		$namespace = $extension->getNamespace();
		if (strlen($namespace) < 1 || preg_match('{[^a-zA-Z0-9]}', $namespace) === 1) {
			throw new Exception('invalid namespace "'.$namespace.'"');
		}
		if (isset($this->extensions[$namespace])) {
			throw new DuplicateException('an extension with namespace "'.$namespace.'" is already applied');
		}
		
		$this->extensions[$namespace] = $extension;
		
		if (isset($this->jsonapi)) {
			$this->jsonapi->addExtension($extension);
		}
	}
	
	/**
	 * apply a profile which adds the link and sets a correct content-type
	 * 
	 * note that the rules from the profile are not automatically enforced
	 * applying the rules, and applying them correctly, is manual
	 * however the $profile could have custom methods to help
	 * 
	 * @see https://jsonapi.org/extensions/#profiles
	 */
	public function applyProfile(ProfileInterface $profile): void {
		$this->profiles[] = $profile;
		
		if (isset($this->jsonapi)) {
			$this->jsonapi->addProfile($profile);
		}
	}
	
	/**
	 * DocumentInterface
	 */
	
	public function toArray(): array {
		$array = [];
		
		if ($this->hasAtMembers()) {
			$array = [...$array, ...$this->getAtMembers()];
		}
		if ($this->hasExtensionMembers()) {
			$array = [...$array, ...$this->getExtensionMembers()];
		}
		
		if (isset($this->jsonapi) && $this->jsonapi->isEmpty() === false) {
			$array['jsonapi'] = $this->jsonapi->toArray();
		}
		if ($this->hasLinks()) {
			$array['links'] = $this->links->toArray();
		}
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * @throws \JsonException
	 */
	public function toJson(array $options=[]): string {
		$options = [...self::$defaults, ...$options];
		
		$array = $options['array'] ?? $this->toArray();
		
		if ($options['prettyPrint']) {
			$options['encodeOptions'] |= JSON_PRETTY_PRINT;
		}
		
		$json = json_encode($array, $options['encodeOptions']);
		
		if ($options['jsonpCallback'] !== null) {
			$json = $options['jsonpCallback'].'('.$json.')';
		}
		
		return $json;
	}
	
	public function sendResponse(array $options=[]): void {
		$options = [...self::$defaults, ...$options];
		
		if ($this->httpStatusCode === 204) {
			http_response_code($this->httpStatusCode);
			return;
		}
		
		$json = $options['json'] ?? $this->toJson($options);
		
		http_response_code($this->httpStatusCode);
		
		$contentType = Converter::prepareContentType($options['contentType'], $this->extensions, $this->profiles);
		header('Content-Type: '.$contentType);
		
		echo $json;
	}
	
	/**
	 * JsonSerializable
	 */
	
	#[\ReturnTypeWillChange]
	public function jsonSerialize(): array {
		return $this->toArray();
	}
}
