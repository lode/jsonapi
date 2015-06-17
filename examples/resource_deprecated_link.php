<?php

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * the resource you want to send out
 * 
 * normally, you'd fetch this from a database
 */

require 'dataset.php';

$user = new user(42);

/**
 * building up the json response
 * 
 * in v1.2.0 resource->add_link() changed it arguments
 * it now accepts only a string a $link ..
 * .. and other properties should be provided separate via $meta_data
 * 
 * these calls show that all old usage of the method stays working for now
 * only the second one (update) is going to be deprecated later on ..
 * .. and will trigger a deprecated error from now on
 * 
 * the fourth call was never possible and will throw an exception
 * this is only possible if using the new $meta_data ..
 * .. and using the old way of providing a mixed $link
 */

$jsonapi = new \alsvanzelf\jsonapi\resource($type='user', $user->id);

$jsonapi->add_link('create', $link='?action=create');
$jsonapi->add_link('update', $link=array('self'=>'?action=update', 'label'=>'Update'));
$jsonapi->add_link('delete', $link='?action=delete', $meta_data=array('label'=>'Delete'));
//$jsonapi->add_link('read', $link=array('self'=>'?action=read'), $meta_data=array('label'=>'Read'));

/**
 * sending the response
 */

$jsonapi->send_response();
