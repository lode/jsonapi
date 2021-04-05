<?php

namespace alsvanzelf\jsonapi\profiles;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\interfaces\PaginableInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ErrorObject;
use alsvanzelf\jsonapi\objects\LinkObject;

/**
 * cursor-based pagination (aka keyset pagination) is a common pagination strategy that avoids many of the pitfalls of 'offset–limit' pagination
 * 
 * @see https://jsonapi.org/profiles/ethanresnick/cursor-pagination/
 * 
 * related query parameters:
 * - sort
 * - page[size]
 * - page[before]
 * - page[after]
 * 
 * handling different use cases:
 * 
 * 1. handle requests with 'after' nor 'before' when the client requests a first page
 *    call {@see setLinksFirstPage} with the last cursor in the first page
 * 
 * 2. handle requests with 'after' when the client requests a next page
 *    call {@see setLinks} with the first and last cursor in the current page
 *    call {@see setLinksLastPage} with the first cursor in the current page, when this is the last page
 * 
 * 3. handle requests with 'before' when the client requests a previous page
 *    call {@see setLinks} with the first and last cursor in the current page
 * 
 * 4. handle requests with 'after' and 'before' when the client requests a specific page
 *    call {@see setLinks} with the first and last cursor in the current page, when there are previous/next pages
 *    call {@see setPaginationLinkObjectsExplicitlyEmpty}, when the results is everything between after and before
 * 
 * other interesting methods:
 * - {@see setCursor} to expose the cursor of a pagination item to allow custom url building
 * - {@see setCount} to expose the total count(s) of the pagination data
 * - {@see get*ErrorObject} to generate ErrorObjects for specific error cases
 * - {@see generatePreviousLink} {@see generateNextLink} to apply the links manually
 */
class CursorPaginationProfile implements ProfileInterface {
	/**
	 * human api
	 */
	
	/**
	 * set links to paginate the data using cursors of the paginated data
	 * 
	 * @param PaginableInterface $paginable        a CollectionDocument or RelationshipObject
	 * @param string             $baseOrCurrentUrl
	 * @param string             $firstCursor
	 * @param string             $lastCursor
	 */
	public function setLinks(PaginableInterface $paginable, $baseOrCurrentUrl, $firstCursor, $lastCursor) {
		$previousLinkObject = new LinkObject($this->generatePreviousLink($baseOrCurrentUrl, $firstCursor));
		$nextLinkObject     = new LinkObject($this->generateNextLink($baseOrCurrentUrl, $lastCursor));
		
		$this->setPaginationLinkObjects($paginable, $previousLinkObject, $nextLinkObject);
	}
	
	/**
	 * @param PaginableInterface $paginable        a CollectionDocument or RelationshipObject
	 * @param string             $baseOrCurrentUrl
	 * @param string             $lastCursor
	 */
	public function setLinksFirstPage(PaginableInterface $paginable, $baseOrCurrentUrl, $lastCursor) {
		$this->setPaginationLinkObjectsWithoutPrevious($paginable, $baseOrCurrentUrl, $lastCursor);
	}
	
	/**
	 * @param PaginableInterface $paginable        a CollectionDocument or RelationshipObject
	 * @param string             $baseOrCurrentUrl
	 * @param string             $firstCursor
	 */
	public function setLinksLastPage(PaginableInterface $paginable, $baseOrCurrentUrl, $firstCursor) {
		$this->setPaginationLinkObjectsWithoutNext($paginable, $baseOrCurrentUrl, $firstCursor);
	}
	
	/**
	 * set the cursor of a specific resource to allow pagination after or before this resource
	 * 
	 * @param ResourceInterface $resource
	 * @param string            $cursor
	 */
	public function setCursor(ResourceInterface $resource, $cursor) {
		$this->setItemMeta($resource, $cursor);
	}
	
	/**
	 * set count(s) to tell about the (estimated) total size
	 * 
	 * @param PaginableInterface $paginable        a CollectionDocument or RelationshipObject
	 * @param int                $exactTotal       optional
	 * @param int                $bestGuessTotal   optional
	 */
	public function setCount(PaginableInterface $paginable, $exactTotal=null, $bestGuessTotal=null) {
		$this->setPaginationMeta($paginable, $exactTotal, $bestGuessTotal);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * helper to get generate a correct page[before] link, use to apply manually
	 * 
	 * @param  string $baseOrCurrentUrl
	 * @param  string $beforeCursor
	 * @return string
	 */
	public function generatePreviousLink($baseOrCurrentUrl, $beforeCursor) {
		return $this->setQueryParameter($baseOrCurrentUrl, 'page[before]', $beforeCursor);
	}
	
	/**
	 * helper to get generate a correct page[after] link, use to apply manually
	 * 
	 * @param  string $baseOrCurrentUrl
	 * @param  string $afterCursor
	 * @return string
	 */
	public function generateNextLink($baseOrCurrentUrl, $afterCursor) {
		return $this->setQueryParameter($baseOrCurrentUrl, 'page[after]', $afterCursor);
	}
	
	/**
	 * pagination links are inside the links object that is a sibling of the paginated data
	 * 
	 * ends up at one of:
	 * - /links/prev                          & /links/next
	 * - /data/relationships/foo/links/prev   & /data/relationships/foo/links/next
	 * - /data/0/relationships/foo/links/prev & /data/0/relationships/foo/links/next
	 * 
	 * @see https://jsonapi.org/profiles/ethanresnick/cursor-pagination/#terms-pagination-links
	 * 
	 * @param PaginableInterface $paginable
	 * @param LinkObject         $previousLinkObject
	 * @param LinkObject         $nextLinkObject
	 */
	public function setPaginationLinkObjects(PaginableInterface $paginable, LinkObject $previousLinkObject, LinkObject $nextLinkObject) {
		$paginable->addLinkObject('prev', $previousLinkObject);
		$paginable->addLinkObject('next', $nextLinkObject);
	}
	
	/**
	 * @param PaginableInterface $paginable
	 * @param string             $baseOrCurrentUrl
	 * @param string             $firstCursor
	 */
	public function setPaginationLinkObjectsWithoutNext(PaginableInterface $paginable, $baseOrCurrentUrl, $firstCursor) {
		$this->setPaginationLinkObjects($paginable, new LinkObject($this->generatePreviousLink($baseOrCurrentUrl, $firstCursor)), new LinkObject());
	}
	
	/**
	 * @param PaginableInterface $paginable
	 * @param string             $baseOrCurrentUrl
	 * @param string             $lastCursor
	 */
	public function setPaginationLinkObjectsWithoutPrevious(PaginableInterface $paginable, $baseOrCurrentUrl, $lastCursor) {
		$this->setPaginationLinkObjects($paginable, new LinkObject(), new LinkObject($this->generateNextLink($baseOrCurrentUrl, $lastCursor)));
	}
	
	/**
	 * @param PaginableInterface $paginable
	 */
	public function setPaginationLinkObjectsExplicitlyEmpty(PaginableInterface $paginable) {
		$this->setPaginationLinkObjects($paginable, new LinkObject(), new LinkObject());
	}
	
	/**
	 * pagination item metadata is the page meta at the top-level of a paginated item
	 * 
	 * ends up at one of:
	 * - /data/meta/page
	 * - /data/relationships/foo/meta/page
	 * - /data/0/relationships/foo/meta/page
	 * 
	 * @see https://jsonapi.org/profiles/ethanresnick/cursor-pagination/#terms-pagination-item-metadata
	 * 
	 * @param ResourceInterface $resource
	 * @param string            $cursor
	 */
	public function setItemMeta(ResourceInterface $resource, $cursor) {
		$metadata = [
			'cursor' => $cursor,
		];
		
		if ($resource instanceof ResourceDocument) {
			$resource->addMeta('page', $metadata, $level=Document::LEVEL_RESOURCE);
		}
		else {
			$resource->addMeta('page', $metadata);
		}
	}
	
	/**
	 * pagination metadata is the page meta that is a sibling of the paginated data (and pagination links)
	 * 
	 * ends up at one of:
	 * - /meta/page/total                          & /meta/page/estimatedTotal/bestGuess                          & /meta/page/rangeTruncated
	 * - /data/relationships/foo/meta/page/total   & /data/relationships/foo/meta/page/estimatedTotal/bestGuess   & /data/relationships/foo/meta/page/rangeTruncated
	 * - /data/0/relationships/foo/meta/page/total & /data/0/relationships/foo/meta/page/estimatedTotal/bestGuess & /data/0/relationships/foo/meta/page/rangeTruncated
	 * 
	 * @see https://jsonapi.org/profiles/ethanresnick/cursor-pagination/#terms-pagination-metadata
	 * 
	 * @param PaginableInterface $paginable
	 * @param int                $exactTotal       optional
	 * @param int                $bestGuessTotal   optional
	 * @param boolean            $rangeIsTruncated optional, if both after and before are supplied but the items exceed requested or max size
	 */
	public function setPaginationMeta(PaginableInterface $paginable, $exactTotal=null, $bestGuessTotal=null, $rangeIsTruncated=null) {
		$metadata = [];
		
		if ($exactTotal !== null) {
			$metadata['total'] = $exactTotal;
		}
		if ($bestGuessTotal !== null) {
			$metadata['estimatedTotal'] = [
				'bestGuess' => $bestGuessTotal,
			];
		}
		if ($rangeIsTruncated !== null) {
			$metadata['rangeTruncated'] = $rangeIsTruncated;
		}
		
		$paginable->addMeta('page', $metadata);
	}
	
	/**
	 * get an ErrorObject for when the requested sorting cannot efficiently be paginated
	 * 
	 * ends up at:
	 * - /errors/0/code
	 * - /errors/0/status
	 * - /errors/0/source/parameter
	 * - /errors/0/links/type/0
	 * - /errors/0/title            optional
	 * - /errors/0/detail           optional
	 * 
	 * @param  string $genericTitle    optional
	 * @param  string $specificDetails optional
	 * @return ErrorObject
	 */
	public function getUnsupportedSortErrorObject($genericTitle=null, $specificDetails=null) {
		$errorObject = new ErrorObject('Unsupported sort');
		$errorObject->appendTypeLink('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/unsupported-sort');
		$errorObject->blameQueryParameter('sort');
		$errorObject->setHttpStatusCode(400);
		
		if ($genericTitle !== null) {
			$errorObject->setHumanExplanation($genericTitle, $specificDetails);
		}
		
		return $errorObject;
	}
	
	/**
	 * get an ErrorObject for when the requested page size exceeds the server-defined max page size
	 * 
	 * ends up at:
	 * - /errors/0/code
	 * - /errors/0/status
	 * - /errors/0/source/parameter
	 * - /errors/0/links/type/0
	 * - /errors/0/meta/page/maxSize
	 * - /errors/0/title             optional
	 * - /errors/0/detail            optional
	 * 
	 * @param  int    $maxSize
	 * @param  string $genericTitle    optional, e.g. 'Page size requested is too large.'
	 * @param  string $specificDetails optional, e.g. 'You requested a size of 200, but 100 is the maximum.'
	 * @return ErrorObject
	 */
	public function getMaxPageSizeExceededErrorObject($maxSize, $genericTitle=null, $specificDetails=null) {
		$errorObject = new ErrorObject('Max page size exceeded');
		$errorObject->appendTypeLink('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/max-size-exceeded');
		$errorObject->blameQueryParameter('page[size]');
		$errorObject->setHttpStatusCode(400);
		$errorObject->addMeta('page', $value=['maxSize' => $maxSize]);
		
		if ($genericTitle !== null) {
			$errorObject->setHumanExplanation($genericTitle, $specificDetails);
		}
		
		return $errorObject;
	}
	
	/**
	 * get an ErrorObject for when the requested page size is not a positive integer, or when the requested page after/before is not a valid cursor
	 * 
	 * ends up at:
	 * - /errors/0/code
	 * - /errors/0/status
	 * - /errors/0/source/parameter
	 * - /errors/0/links/type/0     optional
	 * - /errors/0/title            optional
	 * - /errors/0/detail           optional
	 * 
	 * @param  int    $queryParameter  e.g. 'sort' or 'page[size]'
	 * @param  string $typeLink        optional
	 * @param  string $genericTitle    optional, e.g. 'Invalid Parameter.'
	 * @param  string $specificDetails optional, e.g. 'page[size] must be a positive integer; got 0'
	 * @return ErrorObject
	 */
	public function getInvalidParameterValueErrorObject($queryParameter, $typeLink=null, $genericTitle=null, $specificDetails=null) {
		$errorObject = new ErrorObject('Invalid parameter value');
		$errorObject->blameQueryParameter($queryParameter);
		$errorObject->setHttpStatusCode(400);
		
		if ($typeLink !== null) {
			$errorObject->appendTypeLink($typeLink);
		}
		
		if ($genericTitle !== null) {
			$errorObject->setHumanExplanation($genericTitle, $specificDetails);
		}
		
		return $errorObject;
	}
	
	/**
	 * get an ErrorObject for when range pagination requests (when both 'page[after]' and 'page[before]' are requested) are not supported
	 * 
	 * ends up at:
	 * - /errors/0/code
	 * - /errors/0/status
	 * - /errors/0/links/type/0
	 * 
	 * @param  string $genericTitle    optional
	 * @param  string $specificDetails optional
	 * @return ErrorObject
	 */
	public function getRangePaginationNotSupportedErrorObject($genericTitle=null, $specificDetails=null) {
		$errorObject = new ErrorObject('Range pagination not supported');
		$errorObject->appendTypeLink('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/range-pagination-not-supported');
		$errorObject->setHttpStatusCode(400);
		
		if ($genericTitle !== null) {
			$errorObject->setHumanExplanation($genericTitle, $specificDetails);
		}
		
		return $errorObject;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * add or adjust a key in the query string of a url
	 * 
	 * @param string $url
	 * @param string $key
	 * @param string $value
	 */
	private function setQueryParameter($url, $key, $value) {
		$originalQuery     = parse_url($url, PHP_URL_QUERY);
		$decodedQuery      = urldecode($originalQuery);
		$originalIsEncoded = ($decodedQuery !== $originalQuery);
		
		$originalParameters = [];
		parse_str($decodedQuery, $originalParameters);
		
		$newParameters = [];
		parse_str($key.'='.$value, $newParameters);
		
		$fullParameters = array_replace_recursive($originalParameters, $newParameters);
		
		$newQuery = http_build_query($fullParameters);
		if ($originalIsEncoded === false) {
			$newQuery = urldecode($newQuery);
		}
		
		$newUrl = str_replace($originalQuery, $newQuery, $url);
		
		return $newUrl;
	}
	
	/**
	 * ProfileInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function getOfficialLink() {
		return 'https://jsonapi.org/profiles/ethanresnick/cursor-pagination/';
	}
	
	/**
	 * returns the keyword without aliasing
	 * 
	 * @deprecated since aliasing was removed from the profiles spec
	 * 
	 * @param  string $keyword
	 * @return string
	 */
	public function getKeyword($keyword) {
		return $keyword;
	}
}
