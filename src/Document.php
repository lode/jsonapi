<?php

namespace alsvanzelf\jsonapi;

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
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;

/**
 * @see ResourceDocument, CollectionDocument, ErrorsDocument or MetaDocument
 */
abstract class Document implements DocumentInterface, \JsonSerializable {
	use AtMemberManager, ExtensionMemberManager, HttpStatusCodeManager, LinksManager;
	
	const JSONAPI_VERSION_1_0 = '1.0';
	const JSONAPI_VERSION_1_1 = '1.1';
	const JSONAPI_VERSION_LATEST = Document::JSONAPI_VERSION_1_1;
	
	const CONTENT_TYPE_OFFICIAL = 'application/vnd.api+json';
	const CONTENT_TYPE_DEBUG    = 'application/json';
	const CONTENT_TYPE_JSONP    = 'application/javascript';
	
	const LEVEL_ROOT     = 'root';
	const LEVEL_JSONAPI  = 'jsonapi';
	const LEVEL_RESOURCE = 'resource';
	
	/** @var MetaObject */
	protected $meta;
	/** @var JsonapiObject */
	protected $jsonapi;
	/** @var ExtensionInterface[] */
	protected $extensions = [];
	/** @var ProfileInterface[] */
	protected $profiles = [];
	/** @var array */
	protected static $defaults = [
		/**
		 * encode to json with these default options
		 */
		'encodeOptions' => JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE,
		
		/**
		 * encode to human-readable json, useful when debugging
		 */
		'prettyPrint' => false,
		
		/**
		 * send out the official jsonapi content-type header
		 * overwrite for jsonp or if clients don't support it
		 */
		'contentType' => Document::CONTENT_TYPE_OFFICIAL,
		
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
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 * 
	 * @throws InputException if the $level is Document::LEVEL_JSONAPI, Document::LEVEL_RESOURCE, or unknown
	 */
	public function addLink($key, $href, array $meta=[], $level=Document::LEVEL_ROOT) {
		if ($level === Document::LEVEL_ROOT) {
			if ($this->links === null) {
				$this->setLinksObject(new LinksObject());
			}
			
			$this->links->add($key, $href, $meta);
		}
		elseif ($level === Document::LEVEL_JSONAPI) {
			throw new InputException('level "jsonapi" can not be used for links');
		}
		elseif ($level === Document::LEVEL_RESOURCE) {
			throw new InputException('level "resource" can only be set on a ResourceDocument');
		}
		else {
			throw new InputException('unknown level "'.$level.'"');
		}
	}
	
	/**
	 * set the self link on the document
	 * 
	 * @note a LinkObject is added when extensions or profiles are applied
	 * 
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 */
	public function setSelfLink($href, array $meta=[], $level=Document::LEVEL_ROOT) {
		if ($level === Document::LEVEL_ROOT && ($this->extensions !== [] || $this->profiles !== [])) {
			$contentType = Converter::prepareContentType(Document::CONTENT_TYPE_OFFICIAL, $this->extensions, $this->profiles);
			
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
	 * @note according to the spec, this can only be set to Document::LEVEL_ROOT
	 * 
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setDescribedByLink($href, array $meta=[]) {
		$this->addLink('describedby', $href, $meta, $level=Document::LEVEL_ROOT);
	}
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 * 
	 * @throws InputException if the $level is unknown
	 * @throws InputException if the $level is Document::LEVEL_RESOURCE
	 */
	public function addMeta($key, $value, $level=Document::LEVEL_ROOT) {
		if ($level === Document::LEVEL_ROOT) {
			if ($this->meta === null) {
				$this->setMetaObject(new MetaObject());
			}
			
			$this->meta->add($key, $value);
		}
		elseif ($level === Document::LEVEL_JSONAPI) {
			if ($this->jsonapi === null) {
				$this->setJsonapiObject(new JsonapiObject());
			}
			
			$this->jsonapi->addMeta($key, $value);
		}
		elseif ($level === Document::LEVEL_RESOURCE) {
			throw new InputException('level "resource" can only be set on a ResourceDocument');
		}
		else {
			throw new InputException('unknown level "'.$level.'"');
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * @param JsonapiObject $jsonapiObject
	 */
	public function setJsonapiObject(JsonapiObject $jsonapiObject) {
		$this->jsonapi = $jsonapiObject;
	}
	
	/**
	 * hide that this api supports jsonapi, or which version it is using
	 */
	public function unsetJsonapiObject() {
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
	 * @param ExtensionInterface $extension
	 * 
	 * @throws Exception if namespace uses illegal characters
	 * @throws DuplicateException if namespace conflicts with another applied extension
	 */
	public function applyExtension(ExtensionInterface $extension) {
		$namespace = $extension->getNamespace();
		if (strlen($namespace) < 1 || preg_match('{[^a-zA-Z0-9]}', $namespace) === 1) {
			throw new Exception('invalid namespace "'.$namespace.'"');
		}
		if (isset($this->extensions[$namespace])) {
			throw new DuplicateException('an extension with namespace "'.$namespace.'" is already applied');
		}
		
		$this->extensions[$namespace] = $extension;
		
		if ($this->jsonapi !== null) {
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
	 * 
	 * @param ProfileInterface $profile
	 */
	public function applyProfile(ProfileInterface $profile) {
		$this->profiles[] = $profile;
		
		if ($this->jsonapi !== null) {
			$this->jsonapi->addProfile($profile);
		}
	}
	
	/**
	 * DocumentInterface
	 */
	
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
		
		if ($this->jsonapi !== null && $this->jsonapi->isEmpty() === false) {
			$array['jsonapi'] = $this->jsonapi->toArray();
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toJson(array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		$array = ($options['array'] !== null) ? $options['array'] : $this->toArray();
		
		if ($options['prettyPrint']) {
			$options['encodeOptions'] |= JSON_PRETTY_PRINT;
		}
		
		$json = json_encode($array, $options['encodeOptions']);
		if ($json === false) {
			throw new Exception('failed to generate json: '.json_last_error_msg());
		}
		
		if ($options['jsonpCallback'] !== null) {
			$json = $options['jsonpCallback'].'('.$json.')';
		}
		
		return $json;
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendResponse(array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		if ($this->httpStatusCode === 204) {
			http_response_code($this->httpStatusCode);
			return;
		}
		
		$json = ($options['json'] !== null) ? $options['json'] : $this->toJson($options);
		
		http_response_code($this->httpStatusCode);
		
		$contentType = Converter::prepareContentType($options['contentType'], $this->extensions, $this->profiles);
		header('Content-Type: '.$contentType);
		
		echo $json;
	}
	
	/**
	 * JsonSerializable
	 */
	
	public function jsonSerialize() {
		return $this->toArray();
	}
}
