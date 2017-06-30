<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

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
$self_meta      = ['level' => 'data & root'];
$backwards_meta = ['level' => 'data only'];
$partner_meta   = ['level' => 'data only'];
$redirect_meta  = ['level' => 'data & root'];

$jsonapi->set_self_link('/user/42', $self_meta);
$jsonapi->add_link('backwards', '/compatible', $backwards_meta); // also_root = default = data only
$jsonapi->add_link('partner',   '/user/1',     $partner_meta,  $also_root=false);
$jsonapi->add_link('redirect',  '/login',      $redirect_meta, $also_root=true);

/**
 * sending the response
 */

$jsonapi->send_response();
