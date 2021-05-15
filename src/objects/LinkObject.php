<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\MetaObject;

class LinkObject implements ObjectInterface {
	use AtMemberManager;
	
	/** @var string */
	protected $href;
	/** @var string */
	protected $rel;
	/** @var LinkObject */
	protected $describedby;
	/** @var string */
	protected $title;
	/** @var string */
	protected $type;
	/** @var string[] */
	protected $hreflang = [];
	/** @var MetaObject */
	protected $meta;
	
	/**
	 * @param string $href
	 * @param array  $meta optional
	 */
	public function __construct($href=null, array $meta=[]) {
		if ($href !== null) {
			$this->setHref($href);
		}
		if ($meta !== []) {
			$this->setMetaObject(MetaObject::fromArray($meta));
		}
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param string $href
	 */
	public function setDescribedBy($href) {
		$this->setDescribedByLinkObject(new LinkObject($href));
	}
	
	/**
	 * @param string $language
	 */
	public function addLanguage($language) {
		if ($this->hreflang === []) {
			$this->setHreflang($language);
		}
		else {
			$this->setHreflang(...array_merge($this->hreflang, [$language]));
		}
	}
	
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
	 * @param string $href
	 */
	public function setHref($href) {
		$this->href = $href;
	}
	
	/**
	 * @param string $relationType
	 */
	public function setRelationType($relationType) {
		$this->rel = $relationType;
	}
	
	/**
	 * @param LinkObject $describedBy
	 */
	public function setDescribedByLinkObject(LinkObject $describedBy) {
		$this->describedby = $describedBy;
	}
	
	/**
	 * @param string $friendlyTitle
	 */
	public function setHumanTitle($humanTitle) {
		$this->title = $humanTitle;
	}
	
	/**
	 * @param string $mediaType
	 */
	public function setMediaType($mediaType) {
		$this->type = $mediaType;
	}
	
	/**
	 * @param string ...$hreflang
	 */
	public function setHreflang(...$hreflang) {
		$this->hreflang = $hreflang;
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
		if ($this->href !== null) {
			return false;
		}
		if ($this->rel !== null) {
			return false;
		}
		if ($this->title !== null) {
			return false;
		}
		if ($this->type !== null) {
			return false;
		}
		if ($this->hreflang !== []) {
			return false;
		}
		if ($this->describedby !== null && $this->describedby->isEmpty() === false) {
			return false;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			return false;
		}
		if ($this->hasAtMembers()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = $this->getAtMembers();
		
		$array['href'] = $this->href;
		
		if ($this->rel !== null) {
			$array['rel'] = $this->rel;
		}
		if ($this->title !== null) {
			$array['title'] = $this->title;
		}
		if ($this->type !== null) {
			$array['type'] = $this->type;
		}
		if ($this->hreflang !== []) {
			if (count($this->hreflang) === 1) {
				$array['hreflang'] = $this->hreflang[0];
			}
			else {
				$array['hreflang'] = $this->hreflang;
			}
		}
		if ($this->describedby !== null && $this->describedby->isEmpty() === false) {
			$array['describedby'] = $this->describedby->toArray();
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
