<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * preparing base data and nesting relationships
 */

require 'dataset.php';

$user = new user(42);

$flap = new jsonapi\resource('flap', 1);
$flap->add_data('color', 'orange');

$wing = new jsonapi\resource('wing', 1);
$wing->add_data('side', 'top');
$wing->add_relation('flap', $flap);

$ship = new jsonapi\resource('ship', 5);
$ship->add_data('name', 'Heart of Gold');
$ship->add_relation('wing', $wing);

/**
 * building up the json response
 */

$jsonapi = new jsonapi\resource($type='user', $user->id);
$jsonapi->fill_data($user);
$jsonapi->add_relation('ship', $ship);

/**
 * sending the response
 */

$jsonapi->send_response();
