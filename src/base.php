<?php

namespace alsvanzelf\jsonapi;

class base {

/**
 * content type headers
 */
const CONTENT_TYPE_OFFICIAL = 'application/vnd.api+json';
const CONTENT_TYPE_DEBUG = 'application/json';

/**
 * internal data containers
 */
protected $links     = array();
protected $meta_data = array();

/**
 * sets the self link using $_SERVER variables
 * 
 * @see ->set_self_link() to override this default behavior
 */
public function __construct() {
	$self_link = $_SERVER['REQUEST_URI'];
	if (isset($_SERVER['PATH_INFO'])) {
		$self_link = $_SERVER['PATH_INFO'];
	}
	
	$this->set_self_link($self_link);
}

/**
 * alias for ->get_json()
 * 
 * @see ->get_json()
 * 
 * @return string
 */
public function __toString() {
	return $this->get_json();
}

/**
 * returns the whole response body as json
 * it generates the response via ->get_array()
 * 
 * @see ->get_array() for the structure
 * @see json_encode() options
 * 
 * @param  int  $encode_options optional, $options for json_encode()
 *                              defaults to JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
 * @return json
 */
public function get_json($encode_options=448) {
	$response = $this->get_array();
	
	$json = json_encode($response, $encode_options);
	
	return $json;
}

/**
 * sends out the json response to the browser
 * this will fetch the response from ->get_json() if not given via $response
 * 
 * @param  string $content_type   optional, defaults to the official IANA registered one
 * @param  int    $encode_options optional, $options for json_encode()
 *                                defaults to JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
 * @return void                   however, a string will be echo'd to the browser
 */
public function send_response($content_type=self::CONTENT_TYPE_OFFICIAL, $encode_options=448, $response=null) {
	if (is_null($response)) {
		$response = $this->get_json($encode_options);
	}
	
	header('Content-Type: '.$content_type);
	echo $response;
}

/**
 * sets the link to the request used to give this response
 * this will end up in response.links.self ..
 * and in response.data.links.self for single resource objects
 * 
 * by default this is already set using $_SERVER variables
 * use this method to override this default behavior
 * @see ::__construct()
 * 
 * @param  string $link
 * @return void
 */
public function set_self_link($link) {
	$this->links['self'] = $link;
}

/**
 * adds an included resource
 * this will end up in response.included.{$key}
 * 
 * a $resource should have its 'id' set
 * 
 * @note this can only be used by resource and collection, not by errors
 * 
 * @param \alsvanzelf\jsonapi\resource $resource
 */
public function add_included_resource(\alsvanzelf\jsonapi\resource $resource) {
	if (property_exists($this, 'included_resources') == false) {
		throw new \Exception(get_class($this).' can not contain included resources');
	}
	
	$resource_array = $resource->get_array();
	if (empty($resource_array['data']['id'])) {
		return;
	}
	
	$resource_array = $resource_array['data'];
	unset($resource_array['relationships'], $resource_array['meta']);
	
	$key = $resource_array['type'].'/'.$resource_array['id'];
	
	$this->included_resources[$key] = $resource_array;
}

/**
 * fills the included resources
 * this will end up in response.included
 * 
 * @param  array $resources of \alsvanzelf\jsonapi\resource objects
 * @return void
 */
public function fill_included_resources($resources) {
	foreach ($resources as $resource) {
		$this->add_included_resource($resource);
	}
}

/**
 * adds some meta data
 * this will end up in response.meta.{$key}
 * 
 * @param  string  $key
 * @param  mixed   $meta_data objects are converted in arrays using their public properties
 * @return void
 */
public function add_meta($key, $meta_data) {
	if (is_scalar($meta_data) == false && is_array($meta_data) == false) {
		$meta_data = parent::convert_to_array($meta_data);
	}
	
	$this->meta_data[$key] = $meta_data;
}

/**
 * fills the meta data
 * this will end up in response.meta
 * 
 * @param  array   $meta_data
 * @return void
 */
public function fill_meta($meta_data) {
	foreach ($meta_data as $key => $single_meta_data) {
		$this->add_meta($key, $single_meta_data);
	}
}

/**
 * converting a non-array to an array
 * 
 * @param  mixed $mixed by default, it is type casted to an array
 *                      if it is an object, its public properties are used
 *                      if it is a \alsvanzelf\jsonapi\resource, its ->get_array() is used
 * @return array
 */
protected static function convert_to_array($mixed) {
	if ($mixed instanceof \alsvanzelf\jsonapi\resource) {
		return $mixed->get_array();
	}
	
	if (is_object($mixed)) {
		return get_object_vars($mixed);
	}
	
	return (array)$mixed;
}

}
