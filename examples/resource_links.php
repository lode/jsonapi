<?php

declare(strict_types=1);

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;

require 'bootstrap_examples.php';

$userEntity = ExampleDataset::getEntity('user', 42);

/**
 * add links in different ways to a resource
 * self links are adding both at root and in data levels
 */
$document = ResourceDocument::fromObject($userEntity, $type='user', $userEntity->id);

$selfResourceMeta = ['level' => DocumentLevelEnum::Resource->name];
$partnerMeta      = ['level' => DocumentLevelEnum::Resource->name];
$redirectMeta     = ['level' => DocumentLevelEnum::Root->name];

$document->setSelfLink('/user/42',        $selfResourceMeta);
$document->addLink('partner',  '/user/1', $partnerMeta,  $level=DocumentLevelEnum::Resource);
$document->addLink('redirect', '/login',  $redirectMeta, $level=DocumentLevelEnum::Root);

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
