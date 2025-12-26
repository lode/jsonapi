<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\objects\AbstractObject;
use alsvanzelf\jsonapi\objects\LinkObject;

class LinksObject extends AbstractObject {
	/** @var array<string, string|LinkObject> */
	protected array $links = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param array<string, ?string> $links key-value with values being href strings
	 */
	public static function fromArray(array $links): LinksObject {
		$linksObject = new self();
		
		foreach ($links as $key => $href) {
			$linksObject->add($key, $href);
		}
		
		return $linksObject;
	}
	
	public static function fromObject(object $links): LinksObject {
		$array = Converter::objectToArray($links);
		
		return self::fromArray($array);
	}
	
	/**
	 * @param array<string, mixed> $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function add(string $key, ?string $href, array $meta=[]): void {
		if ($meta === []) {
			$this->addLinkString($key, $href);
		}
		else {
			$this->addLinkObject($key, new LinkObject($href, $meta));
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @throws DuplicateException if another link is already using that $key
	 */
	public function addLinkString(string $key, ?string $href): void {
		Validator::checkMemberName($key);
		
		if (isset($this->links[$key])) {
			throw new DuplicateException('link with key "'.$key.'" already set');
		}
		
		$this->links[$key] = $href;
	}
	
	/**
	 * @throws DuplicateException if another link is already using that $key
	 */
	public function addLinkObject(string $key, LinkObject $linkObject): void {
		Validator::checkMemberName($key);
		
		if (isset($this->links[$key])) {
			throw new DuplicateException('link with key "'.$key.'" already set');
		}
		
		$this->links[$key] = $linkObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if ($this->links !== []) {
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
		
		foreach ($this->links as $key => $link) {
			if ($link instanceof LinkObject && $link->isEmpty() === false) {
				$array[$key] = $link->toArray();
			}
			elseif ($link instanceof LinkObject && $link->isEmpty()) {
				$array[$key] = null;
			}
			else { // string or null
				$array[$key] = $link;
			}
		}
		
		return $array;
	}
}
