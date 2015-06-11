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
 * @note ease error handling by adding this in your own exception handling
 * 
 * ```
 * public function __toString() {
 *     $jsonapi = new \alsvanzelf\jsonapi\errors($this);
 *     $jsonapi->send_response();
 *     return '';
 * }
 * ```
 */

class errors extends base {

/**
 * advised http status codes
 */
public static $http_status_messages = array(
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
 * internal data containers
 */
protected $links;
protected $errors_collection;
protected $http_status;
protected $meta_data;

/**
 * creates a new errors collection
 * it can be instantiated with a first/single exception/error to start the collection with
 * (further) errors can be added via ->add_exception() or ->add_error() or ->fill_errors()
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
 * this will fetch the response from ->get_json()
 * @note it will also terminate script execution afterwards
 * 
 * @param  string $content_type   optional, defaults to the official IANA registered one
 *                                or to a debug version when ::$debug is set to true
 * @param  int    $encode_options optional, $options for json_encode()
 *                                defaults to JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
 * @return void                   more so, a string will be echo'd to the browser ..
 *                                .. and script execution will terminate
 */
public function send_response($content_type=null, $encode_options=448, $response=null) {
	$http_protocol  = $_SERVER['SERVER_PROTOCOL'];
	$status_message = self::get_http_status_message($this->http_status);
	header($http_protocol.' '.$status_message);
	
	parent::send_response($content_type, $encode_options);
	exit;
}

/**
 * sets the http status code for this error response
 * 
 * @param int $http_status one of the predefined ones in ::$http_status_messages
 *                         else, 500 is set
 */
public function set_http_status($http_status) {
	if (empty($http_status)) {
		return;
	}
	
	$this->http_status = $http_status;
}

/**
 * adds an error to the errors collection
 * this will end up in response.errors[]
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
 * @param object $exception        extending \Exception
 * @param string $friendly_message optional, @see jsonapi\error->set_friendly_message()
 * @param string $about_link       optional, @see jsonapi\error->set_about_link()
 * 
 * @todo hide exception meta data on production environments
 */
public function add_exception($exception=null, $friendly_message=null, $about_link=null) {
	$previous_exception = $exception->getPrevious();
	if ($previous_exception) {
		$this->add_exception($previous_exception);
	}
	
	$error_message = $exception->getMessage();
	$error_status  = $exception->getCode();
	
	$new_error = new error($error_message, $friendly_message, $about_link);
	$new_error->set_http_status($error_status);
	
	// meta data
	if (base::$debug) {
		$trace = $exception->getTrace();
		if ($trace) {
			$new_error->add_meta('trace', $trace);
		}
		
		$file = $exception->getFile();
		if ($file) {
			$new_error->add_meta('file',  $file);
		}
		
		$line = $exception->getLine();
		if ($line) {
			$new_error->add_meta('line',  $line);
		}
	}
	
	$this->add_error_object($new_error);
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
	$this->set_http_status($error_http_status);
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

/**
 * generates a http status string from an status code
 * 
 * @param  int    $status_code one of the predefined ones in ::$http_status_messages
 *                             else, 500 is assumed
 * @return string              the status code with the standard status message
 *                             i.e. "404 Not Found"
 */
public static function get_http_status_message($status_code) {
	if (empty(self::$http_status_messages[$status_code])) {
		$status_code = 500;
	}
	
	return $status_code.' '.self::$http_status_messages[$status_code];
}

}
