<?php

declare(strict_types=1);

use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\helpers\RequestParser;

require __DIR__.'/bootstrap_examples.php';

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
$_SERVER['CONTENT_TYPE']   = ContentTypeEnum::Official->value;

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
echo '<h2>Get self link</h2>';
echo '<pre style="font-size: large;">$requestParser->getSelfLink()</pre>';
echo '<pre>'.var_export($requestParser->getSelfLink(), return: true).'</pre>';

// useful for determining how to process the request (list/get/create/update)
echo '<h2>Check query parameters</h2>';
echo '<pre style="font-size: large;">$requestParser->hasIncludePaths()</pre>';
echo '<pre>'.var_export($requestParser->hasIncludePaths(), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->hasSparseFieldset()</pre>';
echo '<pre>'.var_export($requestParser->hasSparseFieldset('user'), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->hasSortFields()</pre>';
echo '<pre>'.var_export($requestParser->hasSortFields(), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->hasPagination()</pre>';
echo '<pre>'.var_export($requestParser->hasPagination(), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->hasFilter()</pre>';
echo '<pre>'.var_export($requestParser->hasFilter(), return: true).'</pre>';

// these methods often return arrays where comma separated query parameter values are processed for ease of use
echo '<h2>Get query parameters</h2>';
echo '<pre style="font-size: large;">$requestParser->getIncludePaths()</pre>';
echo '<pre>'.var_export($requestParser->getIncludePaths(), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->getSparseFieldset()</pre>';
echo '<pre>'.var_export($requestParser->getSparseFieldset('user'), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->getSortFields()</pre>';
echo '<pre>'.var_export($requestParser->getSortFields(), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->getPagination()</pre>';
echo '<pre>'.var_export($requestParser->getPagination(), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->getFilter()</pre>';
echo '<pre>'.var_export($requestParser->getFilter(), return: true).'</pre>';

// use for determinging whether keys were given without having to dive deep into the POST data yourself
echo '<h2>Check parts of the document</h2>';
echo '<pre style="font-size: large;">$requestParser->hasAttribute()</pre>';
echo '<pre>'.var_export($requestParser->hasAttribute('name'), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->hasRelationship()</pre>';
echo '<pre>'.var_export($requestParser->hasRelationship('ship'), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->hasMeta()</pre>';
echo '<pre>'.var_export($requestParser->hasMeta('lock'), return: true).'</pre>';

// get the raw data from the document, this doesn't (yet) return specific objects
echo '<h2>Get parts of the document</h2>';
echo '<pre style="font-size: large;">$requestParser->getAttribute()</pre>';
echo '<pre>'.var_export($requestParser->getAttribute('name'), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->getRelationship()</pre>';
echo '<pre>'.var_export($requestParser->getRelationship('ship'), return: true).'</pre>';
echo '<pre style="font-size: large;">$requestParser->getMeta()</pre>';
echo '<pre>'.var_export($requestParser->getMeta('lock'), return: true).'</pre>';

// get the full document for custom processing
echo '<h2>Get full document</h2>';
echo '<pre style="font-size: large;">$requestParser->getDocument()</pre>';
echo '<pre>'.var_export($requestParser->getDocument(), return: true).'</pre>';
