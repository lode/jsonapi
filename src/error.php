<?php

namespace alsvanzelf\jsonapi;

/**
 * single error inside an errors collection response
 * 
 * @note this class does *not* serve as a complete response
 *       it is used, and can be used, to fill an errors collection
 */

class error extends base {

/**
 * internal data containers
 */
private $identifier;
private $about_link;
private $http_status;
private $error_message;
private $friendly_message;
private $friendly_detail;
private $post_body_pointer;
private $get_parameter_name;
private $meta_data;

/**
 * http status messages used for string output
 */
private static $http_status_messages = array(
	200 => 'OK',
	201 => 'Created',
	204 => 'No Content',
	304 => 'Not Modified',
	307 => 'Temporary Redirect',
	308 => 'Permanent Redirect',
	400 => 'Bad Request',
	401 => 'Unauthorized',
	403 => 'Forbidden',
	404 => 'Not Found',
	405 => 'Method Not Allowed',
	422 => 'Unprocessable Entity',
	500 => 'Internal Server Error',
	503 => 'Service Unavailable',
);

/**
 * creates a new error for inclusion in the errors collection
 * 
 * @note error message is only shown when debug mode is on (@see base::$debug)
 * 
 * @param string $error_message
 * @param string $friendly_message optional, @see ->set_friendly_message()
 * @param string $about_link       optional, @see ->set_about_link()
 */
public function __construct($error_message, $friendly_message=null, $about_link=null) {
	parent::__construct();
	
	$this->set_error_message($error_message);
	
	if ($friendly_message) {
		$this->set_friendly_message($friendly_message);
	}
	
	if ($about_link) {
		$this->set_about_link($about_link);
	}
}

/**
 * generates an array for inclusion in the whole response body of an errors collection
 * 
 * @note error message (`code`) is only shown when debug mode is on (@see base::$debug)
 * 
 * @see jsonapi.org/format
 * 
 * @return array, containing:
 *         - status
 *         - code
 *         - title
 *         - detail
 *         - source
 *           - pointer
 *           - parameter
 *         - links
 *           - about
 *         - id
 *         - meta
 */
public function get_array() {
	$response_part = array();
	
	// the basics
	$response_part['status'] = $this->http_status;
	if (base::$debug) {
		$response_part['code'] = $this->error_message;
	}
	
	// user guidance
	if ($this->friendly_message) {
		$response_part['title'] = $this->friendly_message;
	}
	if ($this->friendly_detail) {
		$response_part['detail'] = $this->friendly_detail;
	}
	
	// the source of the problem
	if ($this->post_body_pointer || $this->get_parameter_name) {
		$response_part['source'] = array();
		
		if ($this->post_body_pointer) {
			$response_part['source']['pointer'] = $this->post_body_pointer;
		}
		if ($this->get_parameter_name) {
			$response_part['source']['parameter'] = $this->get_parameter_name;
		}
	}
	
	// technical guidance
	if ($this->about_link) {
		$response_part['links'] = array(
			'about' => $this->about_link,
		);
	}
	if ($this->identifier) {
		$response_part['id'] = $this->identifier;
	}
	
	// meta data
	if ($this->meta_data) {
		$response_part['meta'] = $this->meta_data;
	}
	
	return $response_part;
}

/**
 * returns the set status code apart from the response array
 * used by the errors collection to figure out the generic status code
 * 
 * @return int probably one of the predefined ones in jsonapi\response::STATUS_*
 */
public function get_http_status() {
	return (int)$this->http_status;
}

/**
 * sets a status code for the single error
 * this will end up in response.errors[].status
 * 
 * @note this does only hint but not strictly set the actual status code send out to the browser
 *       use jsonapi\errors->set_http_status() to be sure
 * 
 * @param mixed $http_status string:  an http status, should start with the numeric status code
 *                           integer: one of the predefined ones in response::STATUS_* ..
 *                                    .. will be converted to string
 */
public function set_http_status($http_status) {
	if (is_int($http_status)) {
		$http_status = (string)$http_status;
		
		// add status message for a few known ones
		if (isset(self::$http_status_messages[$http_status])) {
			$http_status .= ' '.self::$http_status_messages[$http_status];
		}
	}
	
	$this->http_status = $http_status;
}

/**
 * sets the main error message, aimed at developers
 * this will end up in response.errors[].code
 * 
 * @note error message is only shown when debug mode is on (@see base::$debug)
 * 
 * @param string $error_message
 */
public function set_error_message($error_message) {
	$this->error_message = $error_message;
}

/**
 * sets a main user facing message
 * it should be human friendly and ready to show the user as the main problem
 * this will end up in response.errors[].title
 * 
 * @note keep it short, more information can be added via ->set_friendly_detail()
 * 
 * @param string $friendly_message
 */
public function set_friendly_message($friendly_message) {
	$this->friendly_message = $friendly_message;
}

/**
 * sets a more detailed explanation of the problem, meant to the end user
 * this will end up in response.errors[].detail
 * 
 * @param string $friendly_detail
 */
public function set_friendly_detail($friendly_detail) {
	$this->friendly_detail = $friendly_detail;
}

/**
 * blames a specific field/value pair from the POST body as the source of the problem
 * this will end up in response.errors[].source.pointer
 * 
 * @param  string $post_body_pointer it should point out the field in the jsonapi structure
 *                                   i.e. "/data/attributes/title"
 */
public function blame_post_body($post_body_pointer) {
	$this->post_body_pointer = $post_body_pointer;
}

/**
 * blames a specific GET query string parameter as the source of the problem
 * this will end up in response.errors[].source.parameter
 * 
 * @param  string $get_parameter_name
 */
public function blame_get_parameter($get_parameter_name) {
	$this->get_parameter_name = $get_parameter_name;
}

/**
 * sets a link which can help in solving the problem
 * this will end up in response.errors[].links.about
 * 
 * @param string $about_link
 */
public function set_about_link($about_link) {
	$this->about_link = $about_link;
}

/**
 * sets an id to help identifying the encountered problem
 * this could be an id used by internal logging which can help during a helpdesk issue
 * this will end up in response.errors[].id
 * 
 * @param mixed $identifier can be an int or string
 */
public function set_identifier($identifier) {
	$this->identifier = $identifier;
}

/**
 * adds some meta data
 * this will end up in response.errors[].meta.{$key}
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
