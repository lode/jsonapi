<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * add links in different ways to a resource
 */

require 'dataset.php';
$user = new user(42);
$jsonapi = ResourceDocument::fromObject($user, $type='user', $user->id);

/**
 * self links are adding both at root and in data levels
 */
$selfResourceMeta = ['level' => Document::LEVEL_RESOURCE];
$partnerMeta      = ['level' => Document::LEVEL_RESOURCE];
$redirectMeta     = ['level' => Document::LEVEL_ROOT];

$jsonapi->setSelfLink('/user/42',        $selfResourceMeta);
$jsonapi->addLink('partner',  '/user/1', $partnerMeta,  $level=Document::LEVEL_RESOURCE);
$jsonapi->addLink('redirect', '/login',  $redirectMeta, $level=Document::LEVEL_ROOT);

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$jsonapi->toJson($options);
