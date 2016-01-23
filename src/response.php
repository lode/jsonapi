<?php

namespace alsvanzelf\jsonapi;

class response extends base {

/**
 * advised http status codes
 */
const STATUS_OK                    = 200;
const STATUS_CREATED               = 201;
const STATUS_NO_CONTENT            = 204;
const STATUS_NOT_MODIFIED          = 304;
const STATUS_TEMPORARY_REDIRECT    = 307;
const STATUS_PERMANENT_REDIRECT    = 308;
const STATUS_BAD_REQUEST           = 400;
const STATUS_UNAUTHORIZED          = 401;
const STATUS_FORBIDDEN             = 403;
const STATUS_NOT_FOUND             = 404;
const STATUS_METHOD_NOT_ALLOWED    = 405;
const STATUS_UNPROCESSABLE_ENTITY  = 422;
const STATUS_INTERNAL_SERVER_ERROR = 500;
const STATUS_SERVICE_UNAVAILABLE   = 503;

/**
 * content type headers
 */
const CONTENT_TYPE_OFFICIAL = 'application/vnd.api+json';
const CONTENT_TYPE_DEBUG    = 'application/json';

/**
 * json encode options
 * default is JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
 * in debug mode (@see ::$debug) JSON_PRETTY_PRINT is added
 */
const ENCODE_DEFAULT = 320;
const ENCODE_DEBUG   = 448;

/**
 * whether or not ->send_response() sends out basic status headers
 * if set to true, it sends the status code and the location header
 */
public static $send_status_headers = true;

/**
 * internal data containers
 */
protected $links              = array();
protected $meta_data          = array();
protected $included_resources = array();
protected $http_status        = self::STATUS_OK;
protected $redirect_location  = null;

/**
 * base constructor for all response objects (resource, collection, errors)
 * 
 * a few things are arranged here:
 * - sets the self link using $_SERVER variables
 * 
 * @see ->set_self_link() to override this default behavior
 */
public function __construct() {
	parent::__construct();
	
	// auto-fill the self link based on the current request
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
 *                              defaults to ::ENCODE_DEFAULT or ::ENCODE_DEBUG, @see ::$debug
 * @return json
 */
public function get_json($encode_options=null) {
	if (is_int($encode_options) == false) {
		$encode_options = self::ENCODE_DEFAULT;
	}
	if (base::$debug || strpos($_SERVER['HTTP_ACCEPT'], '/json') == false) {
		$encode_options = self::ENCODE_DEBUG;
	}
	
	$response = $this->get_array();
	
	$json = json_encode($response, $encode_options);
	
	return $json;
}

/**
 * sends out the json response to the browser
 * this will fetch the response from ->get_json() if not given via $response
 * 
 * @note this also sets the needed http headers (status, location and content-type)
 * 
 * @param  string $content_type   optional, defaults to ::CONTENT_TYPE_OFFICIAL (the official IANA registered one) ..
 *                                .. or to ::CONTENT_TYPE_DEBUG, @see ::$debug
 * @param  int    $encode_options optional, $options for json_encode()
 *                                defaults to ::ENCODE_DEFAULT or ::ENCODE_DEBUG, @see ::$debug
 * @param  json   $response       optional, defaults to ::get_json()
 * @return void                   however, a string will be echo'd to the browser
 */
public function send_response($content_type=null, $encode_options=null, $response=null) {
	if (is_null($response) && $this->http_status != self::STATUS_NO_CONTENT) {
		$response = $this->get_json($encode_options);
	}
	
	if (empty($content_type)) {
		$content_type = self::CONTENT_TYPE_OFFICIAL;
	}
	if (base::$debug || strpos($_SERVER['HTTP_ACCEPT'], '/json') == false) {
		$content_type = self::CONTENT_TYPE_DEBUG;
	}
	
	if (self::$send_status_headers) {
		$this->send_status_headers();
	}
	
	header('Content-Type: '.$content_type.'; charset=utf-8');
	
	if ($this->http_status == self::STATUS_NO_CONTENT) {
		return;
	}
	
	echo $response;
}

/**
 * sends out the http status code and optional redirect location
 * defaults to ::STATUS_OK, or ::STATUS_INTERNAL_SERVER_ERROR for an errors response
 * 
 * @return void
 */
private function send_status_headers() {
	if ($this->redirect_location) {
		if ($this->http_status == self::STATUS_OK) {
			$this->set_http_status(self::STATUS_TEMPORARY_REDIRECT);
		}
		
		header('Location: '.$this->redirect_location, $replace=true, $this->http_status);
		return;
	}
	
	http_response_code($this->http_status);
}

/**
 * sets the http status code for this response
 * 
 * @param int $http_status any will do, you can easily pass one of the predefined ones in ::STATUS_*
 */
public function set_http_status($http_status) {
	$this->http_status = $http_status;
}

/**
 * sets a new location the client should follow
 * 
 * @param string $location    absolute url
 */
public function set_redirect_location($location) {
	if (self::$send_status_headers == false && base::$debug) {
		trigger_error('location will not be send out unless response::$send_status_headers is true', E_USER_NOTICE);
	}
	
	$this->redirect_location = $location;
}

/**
 * returns the included resource objects
 * this is used by a collection to work with the actual objects
 * 
 * @return array
 */
public function get_included_resources() {
	return $this->included_resources;
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
 * @param  mixed  $meta_data optional, meta data as key-value pairs
 *                           objects are converted in arrays, @see base::convert_object_to_array()
 * @return void
 */
public function set_self_link($link, $meta_data=null) {
	if ($meta_data) {
		if (is_object($meta_data)) {
			$meta_data = parent::convert_object_to_array($meta_data);
		}
		
		$link = array(
			'href' => $link,
			'meta' => $meta_data,
		);
	}
	
	$this->links['self'] = $link;
}

/**
 * adds meta data to the default self link
 * this will end up in response.links.self.meta.{$key}
 * 
 * @note you can also use ->set_self_link() with the whole meta object at once
 * 
 * @param  string  $key
 * @param  mixed   $meta_data objects are converted in arrays, @see base::convert_object_to_array()
 * @return void
 */
public function add_self_link_meta($key, $meta_data) {
	if (is_object($meta_data)) {
		$meta_data = self::convert_to_array($meta_data);
	}
	
	// converts string-type link
	if (is_string($this->links['self'])) {
		$this->links['self'] = array(
			'href' => $this->links['self'],
			'meta' => array(),
		);
	}
	
	$this->links['self']['meta'][$key] = $meta_data;
}

/**
 * adds an included resource
 * this will end up in response.included[]
 * 
 * prefer using ->add_relation() instead
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
	
	$this->included_data[$key] = $resource_array;
	
	// make a backup of the actual resource, to pass on to a collection
	$this->included_resources[$key] = $resource;
}

/**
 * fills the included resources
 * this will end up in response.included[]
 * 
 * prefer using ->fill_relations() instead
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
 * @param  mixed   $meta_data objects are converted in arrays, @see base::convert_object_to_array()
 * @return void
 */
public function add_meta($key, $meta_data) {
	if (is_object($meta_data)) {
		$meta_data = parent::convert_object_to_array($meta_data);
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

}
