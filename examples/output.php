<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\MetaDocument;

require 'bootstrap_examples.php';

$document = new MetaDocument();
$document->add('foo', 'bar');

/**
 * get the array
 */

echo '<h2>Get the array</h2>';
echo '<pre style="font-size: large;">$document->toArray();</pre>';
echo '<pre>'.var_export($document->toArray(), true).'</pre>';

/**
 * get the json
 */

$options = ['prettyPrint' => true];
echo '<h2>Get the json</h2>';
echo '<pre style="font-size: large;">$document->toJson();</pre>';
echo '<pre>'.var_export($document->toJson($options), true).'</pre>';

/**
 * use own json_encode
 */

$options = ['prettyPrint' => true];
echo '<h2>Use own <code>json_encode()</code></h2>';
echo '<pre style="font-size: large;">json_encode($document, JSON_PRETTY_PRINT);</pre>';
echo '<pre>'.var_export(json_encode($document, JSON_PRETTY_PRINT), true).'</pre>';

/**
 * get custom json (for a non-spec array)
 */

$customArray = $document->toArray();
$customArray['custom'] = 'foo';

$options = ['prettyPrint' => true, 'array' => $customArray];
echo '<h2>Get custom json</h2>';
echo '<pre style="font-size: large;">';
echo '$customArray = $document->toArray();'.PHP_EOL;
echo '$customArray[\'custom\'] = \'foo\';'.PHP_EOL;
echo '$options = [\'array\' => $customArray];'.PHP_EOL;
echo '$document->toJson($options);'.PHP_EOL;
echo '</pre>';
echo '<pre>'.var_export($document->toJson($options), true).'</pre>';

/**
 * get jsonp with callback
 */

$options = ['prettyPrint' => true, 'jsonpCallback' => 'callback'];
echo '<h2>Get jsonp with callback</h2>';
echo '<pre style="font-size: large;">';
echo '$options = [\'jsonpCallback\' => \'callback\'];'.PHP_EOL;
echo '$document->toJson($options);'.PHP_EOL;
echo '</pre>';
echo '<pre>'.var_export($document->toJson($options), true).'</pre>';

/**
 * send json response
 */

$options = ['prettyPrint' => true, 'contentType' => 'text/html'];
echo '<h2>Send json response</h2>';
echo '<pre style="font-size: large;">$document->sendResponse();</pre>';
echo '<pre>';
$document->sendResponse($options);
echo '</pre>';
echo '<p><em>Also sends http status code ('.$document->getHttpStatusCode().') and headers: '.var_export(headers_list(), true).'</em></p>';
