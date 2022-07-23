<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\helpers\RequestParser;

require 'bootstrap_examples.php';

/**
 * preparing request data in superglobals from a webserver
 */
$_GET = [
	'include' => 'ship,ship.wing',
	'fields' => [
		'user' => 'name,location',
	],
	'sort' => 'name,-location',
	'page' => [
		'number' => '2',
		'size'   => '10',
	],
	'filter' => '42',
];
$_POST = [
	'data' => [
		'type'       => 'user',
		'id'         => '42',
		'attributes' => [
			'name' => 'Foo',
		],
		'relationships' => [
			'ship' => [
				'data' => [
					'type' => 'ship',
					'id'   => '42',
				],
			],
		],
	],
	'meta' => [
		'lock' => true,
	],
];

$_SERVER['REQUEST_SCHEME'] = 'https';
$_SERVER['HTTP_HOST']      = 'example.org';
$_SERVER['REQUEST_URI']    = '/user/42?'.http_build_query($_GET);
$_SERVER['CONTENT_TYPE']   = Document::CONTENT_TYPE_OFFICIAL;

/**
 * parsing the request
 * 
 * if you have a PSR request object you can use `$requestParser = RequestParser::fromPsrRequest($request);`
 */
$requestParser = RequestParser::fromSuperglobals();

/**
 * now you can check for certain query parameters and document values in an easy way
 */

// useful for filling a self link in responses
var_dump($requestParser->getSelfLink());

// useful for determining how to process the request (list/get/create/update)
var_dump($requestParser->hasIncludePaths());
var_dump($requestParser->hasAnySparseFieldset());
var_dump($requestParser->hasSparseFieldset('user'));
var_dump($requestParser->hasSortFields());
var_dump($requestParser->hasPagination());
var_dump($requestParser->hasFilter());

// these methods often return arrays where comma separated query parameter values are processed for ease of use
var_dump($requestParser->getIncludePaths());
var_dump($requestParser->getSparseFieldset('user'));
var_dump($requestParser->getSortFields());
var_dump($requestParser->getPagination());
var_dump($requestParser->getFilter());

// use for determinging whether keys were given without having to dive deep into the POST data yourself
var_dump($requestParser->hasAttribute('name'));
var_dump($requestParser->hasRelationship('ship'));
var_dump($requestParser->hasMeta('lock'));

// get the raw data from the document, this doesn't (yet) return specific objects
var_dump($requestParser->getAttribute('name'));
var_dump($requestParser->getRelationship('ship'));
var_dump($requestParser->getMeta('lock'));

// useful for determining how to process the request (list/get/create/update)
var_dump($requestParser->hasQueryParameters());
var_dump($requestParser->hasDocument());

// get the full query parameters or document for custom processing
var_dump($requestParser->getQueryParameters());
var_dump($requestParser->getDocument());
