<?php

namespace alsvanzelf\jsonapi;

class base {

/**
 * content type headers
 */
const CONTENT_TYPE_OFFICIAL = 'application/vnd.api+json';
const CONTENT_TYPE_DEBUG = 'application/json';

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
	$this->add_link($key='self', $link, $data_level=false);
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
	$resource_class_name = '\alsvanzelf\jsonapi\resource';
	if ($mixed instanceof $resource_class_name) {
		return $mixed->get_array();
	}
	
	if (is_object($mixed)) {
		return get_object_vars($mixed);
	}
	
	return (array)$mixed;
}

}
