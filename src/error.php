<?php

namespace alsvanzelf\jsonapi;

/**
 * single error inside an errors collection response
 * 
 * @note this class does *not* serve as a complete response
 *       it is used, and can be used, to fill an errors collection
 */

class error {

private $identifier;
private $about_link;
private $http_status;
private $error_message;
private $friendly_message;
private $friendly_detail;
private $post_body_pointer;
private $get_parameter_name;
private $meta_data;

public function __construct($error_message, $friendly_message=null, $about_link=null) {
	$this->set_error_message($error_message);
	
	if ($friendly_message) {
		$this->set_friendly_message($friendly_message);
	}
	
	if ($about_link) {
		$this->set_about_link($about_link);
	}
}

/**
 * generates an array for the whole response body
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
	if ($this->http_status) {
		$status_message = errors::get_http_status_message($this->http_status);
		$response_part['status'] = $status_message;
	}
	$response_part['code'] = $this->error_message;
	
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

public function get_http_status() {
	return $this->http_status;
}

public function set_identifier($identifier) {
	$this->identifier = $identifier;
}

public function set_about_link($about_link) {
	$this->about_link = $about_link;
}

public function set_http_status($http_status) {
	$this->http_status = $http_status;
}

public function set_error_message($error_message) {
	$this->error_message = $error_message;
}

public function set_friendly_message($friendly_message) {
	$this->friendly_message = $friendly_message;
}

public function set_friendly_detail($friendly_detail) {
	$this->friendly_detail = $friendly_detail;
}

public function blame_post_body($post_body_pointer) {
	$this->post_body_pointer = $post_body_pointer;
}

public function blame_get_parameter($get_parameter_name) {
	$this->get_parameter_name = $get_parameter_name;
}

/**
 * adds some meta data
 * this will end up in response.errors[].meta.{$key}
 * 
 * @param  string  $key
 * @param  mixed   $meta_data objects are converted in arrays using their public properties
 * @return void
 */
public function add_meta($key, $meta_data) {
	if (is_scalar($meta_data) == false && is_array($meta_data) == false) {
		$meta_data = base::convert_to_array($meta_data);
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
