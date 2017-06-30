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
 * add links in different ways to a resource
 */

require 'dataset.php';
$user = new user(42);
$jsonapi = new jsonapi\resource($type='user', $user->id);
$jsonapi->fill_data($user);

/**
 * self links are adding both at root and in data levels
 */
$self_route_meta    = ['level' => jsonapi\resource::LINK_LEVEL_BOTH];
$self_resource_meta = ['level' => jsonapi\resource::LINK_LEVEL_DATA];
$backwards_meta     = ['level' => jsonapi\resource::LINK_LEVEL_DATA];
$partner_meta       = ['level' => jsonapi\resource::LINK_LEVEL_DATA];
$redirect_meta      = ['level' => jsonapi\resource::LINK_LEVEL_ROOT];

$jsonapi->set_self_link('/user/42?example=true', $self_route_meta);
$jsonapi->add_link('self', '/user/42', $self_resource_meta);
$jsonapi->add_link('backwards', '/compatible', $backwards_meta); // level = default = LINK_LEVEL_DATA
$jsonapi->add_link('partner',   '/user/1',     $partner_meta,  $level=jsonapi\resource::LINK_LEVEL_DATA);
$jsonapi->add_link('redirect',  '/login',      $redirect_meta, $level=jsonapi\resource::LINK_LEVEL_ROOT);

/**
 * sending the response
 */

$jsonapi->send_response();
