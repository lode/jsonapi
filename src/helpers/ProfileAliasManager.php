<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\ProfileLinkObject;

abstract class ProfileAliasManager {
	/** @var array */
	private $aliasMapping = [];
	/** @var array */
	private $keywordMapping = [];
	
	/**
	 * ProfileInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function __construct(array $aliases=[]) {
		$officialKeywords = $this->getOfficialKeywords();
		if ($officialKeywords === []) {
			return;
		}
		
		$this->keywordMapping = array_combine($officialKeywords, $officialKeywords);
		if ($aliases === []) {
			return;
		}
		
		foreach ($aliases as $keyword => $alias) {
			if ($alias === $keyword) {
				throw new InputException('an alias should be different from its keyword');
			}
			if (in_array($keyword, $officialKeywords, $strict=true) === false) {
				throw new InputException('unknown keyword "'.$keyword.'" to alias');
			}
			Validator::checkMemberName($alias);
			
			$this->keywordMapping[$keyword] = $alias;
		}
		
		$this->aliasMapping = $aliases;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getKeyword($keyword) {
		if (isset($this->keywordMapping[$keyword]) === false) {
			throw new InputException('unknown keyword "'.$keyword.'"');
		}
		
		return $this->keywordMapping[$keyword];
	}
	
	/**
	 * @inheritDoc
	 */
	abstract public function getOfficialKeywords();
	
	/**
	 * @inheritDoc
	 */
	abstract public function getOfficialLink();
	
	/**
	 * @inheritDoc
	 */
	public function getAliasedLink() {
		if ($this->aliasMapping === []) {
			return $this->getOfficialLink();
		}
		
		return new ProfileLinkObject($this->getOfficialLink(), $this->aliasMapping);
	}
}
