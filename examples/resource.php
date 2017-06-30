<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * settings which will change default from 2.x
 */
jsonapi\resource::$self_link_data_level = jsonapi\resource::SELF_LINK_TYPE;

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
 * you can set arrays, single data points, or whole objects
 * objects are converted into arrays using their public keys
 */

$jsonapi = new jsonapi\resource($type='user', $user->id);

$jsonapi->fill_data($user);

$jsonapi->add_data('location', $user->get_current_location());

/**
 * sending the response
 */

$jsonapi->send_response();
