<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\interfaces\HasMetaInterface;
use alsvanzelf\jsonapi\objects\AbstractObject;
use alsvanzelf\jsonapi\objects\MetaObject;

class LinkObject extends AbstractObject implements HasMetaInterface {
	protected string $href;
	protected string $rel;
	protected LinkObject $describedby;
	protected string $title;
	protected string $type;
	/** @var string[] */
	protected array $hreflang = [];
	protected MetaObject $meta;
	
	/**
	 * @param array<string, mixed> $meta
	 */
	public function __construct(?string $href=null, array $meta=[]) {
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
	
	public function setDescribedBy(string $href): void {
		$this->setDescribedByLinkObject(new LinkObject($href));
	}
	
	public function addLanguage(string $language): void {
		if ($this->hreflang === []) {
			$this->setHreflang($language);
		}
		else {
			$this->setHreflang(...[...$this->hreflang, $language]);
		}
	}
	
	public function addMeta(string $key, mixed $value): void {
		if (isset($this->meta) === false) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * spec api
	 */
	
	public function setHref(string $href): void {
		$this->href = $href;
	}
	
	/**
	 * @todo validate according to https://tools.ietf.org/html/rfc8288#section-2.1
	 */
	public function setRelationType(string $relationType): void {
		$this->rel = $relationType;
	}
	
	public function setDescribedByLinkObject(LinkObject $describedBy): void {
		$this->describedby = $describedBy;
	}
	
	public function setHumanTitle(string $humanTitle): void {
		$this->title = $humanTitle;
	}
	
	public function setMediaType(string $mediaType): void {
		$this->type = $mediaType;
	}
	
	/**
	 * @todo validate according to https://tools.ietf.org/html/rfc5646
	 */
	public function setHreflang(string ...$hreflang): void {
		$this->hreflang = $hreflang;
	}
	
	public function setMetaObject(MetaObject $metaObject): void {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if (isset($this->href)) {
			return false;
		}
		if (isset($this->rel)) {
			return false;
		}
		if (isset($this->title)) {
			return false;
		}
		if (isset($this->type)) {
			return false;
		}
		if ($this->hreflang !== []) {
			return false;
		}
		if (isset($this->describedby) && $this->describedby->isEmpty() === false) {
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
		
		if (isset($this->href)) {
			$array['href'] = $this->href;
		}
		if (isset($this->rel)) {
			$array['rel'] = $this->rel;
		}
		if (isset($this->title)) {
			$array['title'] = $this->title;
		}
		if (isset($this->type)) {
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
		if (isset($this->describedby) && $this->describedby->isEmpty() === false) {
			$array['describedby'] = $this->describedby->toArray();
		}
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
