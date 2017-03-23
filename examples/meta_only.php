<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * there are a few use-cases for sending meta-only responses
 * in such cases, use the response class
 * 
 * prefer to actually send out a resource, error or collection
 */

$jsonapi = new jsonapi\response();
$jsonapi->add_meta('foo', 'bar');

/**
 * sending the response
 */

$jsonapi->send_response();
