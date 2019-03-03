<?php

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapi\profiles\CursorPaginationProfile;

require 'bootstrap_examples.php';

/**
 * use the cursor pagination profile as extension to the document
 */

$profile = new CursorPaginationProfile();

$user1  = new ResourceObject('user', 1);
$user2  = new ResourceObject('user', 2);
$user42 = new ResourceObject('user', 42);

$profile->setCursor($user1,  'ford');
$profile->setCursor($user2,  'arthur');
$profile->setCursor($user42, 'zaphod');

$document = CollectionDocument::fromResources($user1, $user2, $user42);
$document->applyProfile($profile);

$profile->setCount($document, $exactTotal=3, $bestGuessTotal=10);
$profile->setLinksFirstPage($document, $currentUrl='/users?sort=42&page[size]=10', $lastCursor='zaphod');

/**
 * get the json
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
