<?php

declare(strict_types=1);

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\helpers\Converter;

require __DIR__.'/bootstrap_examples.php';

/**
 * use an extension extend the document with new members
 */

$extension = new ExampleVersionExtension();

$document = new ResourceDocument('user', 42);
$document->applyExtension($extension);

$document->add('foo', 'bar');

/**
 * you can apply the rules of the extension manually
 * or use methods of the extension if provided
 */

$extension->setVersion($document, '2019');

/**
 * get the json
 */

$contentType = Converter::prepareContentType(ContentTypeEnum::Official, extensions: [$extension], profiles: []);
echo '<code>Content-Type: '.$contentType.'</code>'.PHP_EOL;

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
