<?php

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;

require 'bootstrap_examples.php';

$articleRecords = ExampleDataset::findRecords('articles');
$commentRecords = ExampleDataset::findRecords('comments');
$peopleRecords  = ExampleDataset::findRecords('people');

/**
 * send multiple entities, called a collection
 * 
 * using the example from jsonapi.org's homepage
 */

$document = new CollectionDocument();

foreach ($articleRecords as $articleId => $articleRecord) {
	$authorId = $articleRecord['authorId'];
	
	$author = ResourceObject::fromArray($peopleRecords[$authorId], 'people', $authorId);
	$author->setSelfLink('http://example.com/people/'.$authorId);
	$authorRelationshipLinks = [
		'self'    => 'http://example.com/articles/'.$articleId.'/relationships/author',
		'related' => 'http://example.com/articles/'.$articleId.'/author',
	];
	
	$comments = [];
	foreach ($commentRecords as $commentId => $commentRecord) {
		$comment = ResourceObject::fromArray($commentRecord, 'comments', $commentId);
		$comment->add('body', $commentRecord['body']);
		$comment->addRelationship('author', new ResourceObject('people', $commentRecord['authorId']));
		$comment->setSelfLink('http://example.com/comments/'.$commentId);
		
		$comments[] = $comment;
	}
	
	$commentsRelationshipLinks = [
		'self'    => 'http://example.com/articles/'.$articleId.'/relationships/comments',
		'related' => 'http://example.com/articles/'.$articleId.'/comments',
	];
	
	$article = new ResourceObject('articles', $articleId);
	$article->add('title', $articleRecord['title']);
	$article->setSelfLink('http://example.com/articles/'.$articleId);
	$article->addRelationship('author', $author, $authorRelationshipLinks);
	$article->addRelationship('comments', $comments, $commentsRelationshipLinks);
	
	$document->addResource($article);
}

$document->setSelfLink('http://example.com/articles');
$document->setPaginationLinks($previous=null, $next='http://example.com/articles?page[offset]=2', $first=null, $last='http://example.com/articles?page[offset]=10');
$document->unsetJsonapiObject();

/**
 * get the json
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
