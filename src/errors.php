<?php

namespace alsvanzelf\jsonapi;

/**
 * error collection array
 * this is a collection of jsonapi\error objects
 * but it can be filled with exceptions or error strings as well
 * 
 * main actions
 * - exception   @see ::__construct() or ->add_exception()
 * - error       @see ::__construct() or ->add_error() or ->fill_errors()
 * - http status @see ->set_http_status()
 * - output      @see ->send_response() or ->get_json()
 * 
 * extra elements
 * - meta data @see ->add_meta() or ->fill_meta()
 * - self link @see ->set_self_link()
 * 
 * @note ease error handling by using a jsonapi\exception
 *       @see examples/errors_exception_direct.php
 *       @see jsonapi\exception::__toString() when you want to use your own exception handling
 */

class errors extends response {

/**
 * internal data containers
 */
protected $links;
protected $errors_collection;
protected $http_status = response::STATUS_INTERNAL_SERVER_ERROR;
protected $meta_data;

/**
 * creates a new errors collection
 * it can be instantiated with a first/single exception/error to start the collection with
 * (further) errors can be added via ->add_exception() or ->add_error() or ->fill_errors()
 * 
 * @note error message (if string) is only shown when debug mode is on (@see base::$debug)
 * 
 * @param mixed  $error_message    optional, can be exception, jsonapi\error object, or string
 * @param string $friendly_message optional, @see jsonapi\error->set_friendly_message()
 * @param string $about_link       optional, @see jsonapi\error->set_about_link()
 */
public function __construct($error_message=null, $friendly_message=null, $about_link=null) {
	parent::__construct();
	
	if ($error_message instanceof \Exception) {
		$this->add_exception($error_message, $friendly_message, $about_link);
		return;
	}
	
	$this->add_error($error_message, $friendly_message, $about_link);
}

/**
 * generates an array for the whole response body
 * 
 * @note error message (`code`) is only shown when debug mode is on (@see base::$debug)
 * 
 * @see jsonapi.org/format
 * 
 * @return array, containing:
 *         - links
 *         - errors []
 *           - status
 *           - code
 *           - title
 *           - detail
 *           - source
 *             - pointer
 *             - parameter
 *           - links
 *             - about
 *           - id
 *           - meta
 *         - meta
 */
public function get_array() {
	$response = array();
	
	// links
	if ($this->links) {
		$response['links'] = $this->links;
	}
	
	// errors
	$response['errors'] = $this->errors_collection;
	
	// meta data
	if ($this->meta_data) {
		$response['meta'] = $this->meta_data;
	}
	
	return $response;
}

/**
 * sends out the json response to the browser
 * this will fetch the response from ->get_json() if not given via $response
 * 
 * @note this is the same as jsonapi\response->send_response() ..
 *       .. but it will also terminate script execution afterwards
 * 
 * @param  string $content_type   optional, defaults to ::CONTENT_TYPE_OFFICIAL (the official IANA registered one) ..
 *                                .. or to ::CONTENT_TYPE_DEBUG, @see ::$debug
 * @param  int    $encode_options optional, $options for json_encode()
 *                                defaults to ::ENCODE_DEFAULT or ::ENCODE_DEBUG, @see ::$debug
 * @param  json   $response       optional, defaults to ::get_json()
 * @param  string $jsonp_callback optional, response as jsonp
 * @return void                   more so, a string will be echo'd to the browser ..
 *                                .. and script execution will terminate
 */
public function send_response($content_type=null, $encode_options=null, $response=null, $jsonp_callback=null) {
	parent::send_response($content_type, $encode_options, $response, $jsonp_callback);
	exit;
}

/**
 * sets the http status code for this errors response
 * 
 * @note this does the same as response->set_http_status() except it forces an error status
 * 
 * @param int $http_status any will do, you can easily pass one of the predefined ones in ::STATUS_*
 */
public function set_http_status($http_status) {
	if ($http_status < 400) {
		// can't use that as http status code
		return;
	}
	
	return parent::set_http_status($http_status);
}

/**
 * adds an error to the errors collection
 * this will end up in response.errors[]
 * 
 * @note error message (if string) is only shown when debug mode is on (@see base::$debug)
 * 
 * @param mixed  $error_message    optional, can be jsonapi\error object or string
 * @param string $friendly_message optional, @see jsonapi\error->set_friendly_message()
 * @param string $about_link       optional, @see jsonapi\error->set_about_link()
 */
public function add_error($error_message=null, $friendly_message=null, $about_link=null) {
	if ($error_message instanceof error == false) {
		$error_message = new error($error_message, $friendly_message, $about_link);
	}
	
	$this->add_error_object($error_message);
}

/**
 * fills the errors collection with an array of jsonapi\error objects
 * this will end up in response.errors[]
 * 
 * @param  array $errors with jsonapi\error objects inside
 * @return void
 */
public function fill_errors($errors) {
	foreach ($errors as $error) {
		$this->add_error_object($error);
	}
}

/**
 * adds an exception as error to the errors collection
 * this will end up in response.errors[]
 * 
 * @note exception meta data (file, line, trace) is only shown when debug mode is on (@see base::$debug)
 * 
 * @param object $exception        extending \Exception
 * @param string $friendly_message optional, @see jsonapi\error->set_friendly_message()
 * @param string $about_link       optional, @see jsonapi\error->set_about_link()
 */
public function add_exception($exception, $friendly_message=null, $about_link=null) {
	$error_message = $exception->getMessage();
	$error_status  = $exception->getCode();
	
	$new_error = new error($error_message, $friendly_message, $about_link);
	if ($error_status) {
		$new_error->set_http_status($error_status);
	}
	
	// meta data
	if (base::$debug) {
		$file = $exception->getFile();
		if ($file) {
			$file = str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $file);
			$new_error->add_meta('file',  $file);
		}
		
		$line = $exception->getLine();
		if ($line) {
			$new_error->add_meta('line',  $line);
		}
		
		$trace = $exception->getTrace();
		if ($trace) {
			foreach ($trace as &$place) {
				if (!empty($place['file'])) {
					$place['file'] = str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', $place['file']);
				}
			}
			$new_error->add_meta('trace', $trace);
		}
	}
	
	$this->add_error_object($new_error);
	
	$previous_exception = $exception->getPrevious();
	if ($previous_exception) {
		$this->add_exception($previous_exception);
	}
}

/**
 * adds a jsonapi\error object to the errors collection
 * used internally by ->add_error(), ->fill_errors() and ->add_exception()
 * 
 * further, a generic http status is gathered from added objects
 * 
 * @param jsonapi\error $error
 */
private function add_error_object(\alsvanzelf\jsonapi\error $error) {
	$error_response_part = $error->get_array();
	$error_http_status   = $error->get_http_status();
	
	$this->errors_collection[] = $error_response_part;
	if ($error_http_status) {
		$this->set_http_status($error_http_status);
	}
}

/**
 * blocks included resources on errors collections
 */
public function add_included_resource(\alsvanzelf\jsonapi\resource $resource) {
	throw new \Exception('can not add included resources to errors, add them as meta data instead');
}

/**
 * blocks included resources on errors collections
 */
public function fill_included_resources($resources) {
	throw new \Exception('can not add included resources to errors, add them as meta data instead');
}

}
