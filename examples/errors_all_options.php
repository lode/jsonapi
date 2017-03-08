<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * setting all options
 */

$error = new jsonapi\error($error_message='too much options', $friendly_message='Please, choose a bit less.', $about_link='www.example.com/options.html');

// more details about the error, for the end user to comsume
$error->set_friendly_detail($friendly_detail='Consult your ...');

// mark the cause of the error
$error->blame_post_body($post_body_pointer='/data/attributes/title');
$error->blame_get_parameter($get_parameter_name='filter');

// an identifier useful for helpdesk purposes
$error->set_identifier($identifier=42);

// add meta data as you would on a normal json response
$error->add_meta($key='foo', $meta_data='bar');
$error->fill_meta($meta_data=['bar' => 'baz']);

// or as object
$meta_object = new stdClass();
$meta_object->property = 'value';
$error->add_meta($key='object', $meta_object);

// the http status code
// @note it is better to set this on the jsonapi\errors object ..
//       .. as only a single one can be consumed by the browser
$error->set_http_status($http_status=jsonapi\response::STATUS_NOT_FOUND);

// if not set during construction, set them here
$error->set_error_message($error_message='too much options');
$error->set_friendly_message($friendly_message='Please, choose a bit less.');
$error->set_about_link($about_link='www.example.com/options.html');

/**
 * prepare multiple error objects for the errors response
 */

$another_error  = new jsonapi\error('kiss', 'Error objects can be small and simple as well.');
$some_exception = new Exception('please don\'t throw things', jsonapi\response::STATUS_INTERNAL_SERVER_ERROR);

/**
 * building up the json response
 * 
 * you can pass the $error object to the constructor ..
 * .. or add multiple errors via ->add_error() or ->add_exception()
 * 
 * further you can force another http status code than what's in the errors
 */

$jsonapi = new jsonapi\errors($error);

$jsonapi->add_error($another_error);
$jsonapi->add_exception($some_exception);

$jsonapi->set_http_status(jsonapi\response::STATUS_BAD_REQUEST);

/**
 * sending the response
 * 
 * @note the response includes debug information based on the display_errors directive
 *       you can tune it with that, or by setting jsonapi\base::$debug to false
 */

$jsonapi->send_response();
