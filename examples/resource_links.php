<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;

require 'bootstrap_examples.php';

$userEntity = ExampleDataset::getEntity('user', 42);

/**
 * add links in different ways to a resource
 * self links are adding both at root and in data levels
 */
$document = ResourceDocument::fromObject($userEntity, $type='user', $userEntity->id);

$selfResourceMeta = ['level' => Document::LEVEL_RESOURCE];
$partnerMeta      = ['level' => Document::LEVEL_RESOURCE];
$redirectMeta     = ['level' => Document::LEVEL_ROOT];

$document->setSelfLink('/user/42',        $selfResourceMeta);
$document->addLink('partner',  '/user/1', $partnerMeta,  $level=Document::LEVEL_RESOURCE);
$document->addLink('redirect', '/login',  $redirectMeta, $level=Document::LEVEL_ROOT);

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
