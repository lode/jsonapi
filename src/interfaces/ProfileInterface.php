<?php

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\LinkObject;

/**
 * @see ProfileAliasManager which implement most of the methods
 */
interface ProfileInterface {
	/**
	 * get a profile with its aliases to keywords of the profile
	 * 
	 * having this in the constructor makes sure the aliases are used from the start
	 * 
	 * @param  array $aliases optional mapping keywords to aliases
	 * 
	 * @throws InputException if the alias is not different from the keyword
	 * @throws InputException if the keyword is not known to the profile
	 * @throws InputException if the alias is not a valid member name
	 */
	public function __construct(array $aliases=[]);
	
	/**
	 * get the keyword or current alias based on the official keyword from the profile
	 * 
	 * e.g. for a profile defining an official keyword 'version', this would return 'version'
	 *      or if ->alias('version', 'v') was called before, this would return 'v'
	 * 
	 * @param  string $keyword
	 * @return string
	 * 
	 * @throws InputException if the keyword is not known to the profile
	 */
	public function getKeyword($keyword);
	
	/**
	 * returns an array of official keywords this profile defines
	 * 
	 * @internal
	 * 
	 * @return string[]
	 */
	public function getOfficialKeywords();
	
	/**
	 * the unique link identifying and describing the profile
	 * 
	 * @internal
	 * 
	 * @return string
	 */
	public function getOfficialLink();
	
	/**
	 * get the official link, or a LinkObject with the link and its aliases
	 * 
	 * optionally also contains the aliases applied
	 * 
	 * @internal
	 * 
	 * @return LinkObject|string
	 */
	public function getAliasedLink();
}
