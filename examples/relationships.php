<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * the different ways of adding relationships to a resource
 */

$jsonapi = new jsonapi\resource('user', 1);

$ship1_resource = new jsonapi\resource('ship', 24);
$ship1_resource->add_data('foo', 'bar');

$ship2_resource = new jsonapi\resource('ship', 42);
$ship2_resource->add_data('bar', 'baz');

$friend1_resource = new jsonapi\resource('user', 24);
$friend1_resource->add_data('foo', 'bar');

$friend2_resource = new jsonapi\resource('user', 42);
$friend2_resource->add_data('bar', 'baz');

$dock_resource = new jsonapi\resource('dock', 3);
$dock_resource->add_data('bar', 'baf');

/**
 * to-one relationship
 */

$jsonapi->add_relation('implicit-ship', $ship1_resource);

/**
 * to-one relationship, explicit variant
 * doesn't add functionality
 */

$jsonapi->add_relation('explicit-ship', $ship1_resource, $skip_include=false, $type=jsonapi\resource::RELATION_TO_ONE);

/**
 * to-one relationship, without included resource
 */

$jsonapi->add_relation('excluded-ship', $ship2_resource, $skip_include=true);

/**
 * to-many relationship, one-by-one
 */

$jsonapi->add_relation('one-by-one-friends', $friend1_resource, $skip_include=false, $type=jsonapi\resource::RELATION_TO_MANY);
$jsonapi->add_relation('one-by-one-friends', $friend2_resource, $skip_include=false, $type=jsonapi\resource::RELATION_TO_MANY);

/**
 * to-many relationship, all-at-once
 */

$friends = new jsonapi\collection('friends');
$friends->add_resource($friend1_resource);
$friends->add_resource($friend2_resource);

$jsonapi->add_relation('implicit-friends', $friends);

/**
 * to-many relationship, explicit variant
 */

$jsonapi->add_relation('explicit-friends', $friends, $skip_include=false, $type=jsonapi\resource::RELATION_TO_MANY);

/**
 * to-many relationship, different types
 */

$jsonapi->add_relation('one-by-one-neighbours', $ship1_resource, $skip_include=false, $type=jsonapi\resource::RELATION_TO_MANY);
$jsonapi->add_relation('one-by-one-neighbours', $dock_resource, $skip_include=false, $type=jsonapi\resource::RELATION_TO_MANY);

/**
 * sending the response
 */

$jsonapi->send_response();
