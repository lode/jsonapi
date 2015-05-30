<?php

namespace alsvanzelf\jsonapi;

/**
 * hint, add this in your own exception handling
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

protected $links;
protected $errors_collection;
protected $http_status;
protected $meta_data;

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
 * [send_response description]
 * afterwards, it will terminate script execution, unless $terminate is set to false
 * 
 * @param  [type]  $content_type   [description]
 * @param  integer $encode_options [description]
 * @return void                   more so, a string will be echo'd to the browser ..
 *                                .. and the script will be terminated
 */
public function send_response($content_type=self::CONTENT_TYPE_OFFICIAL, $encode_options=448) {
	$http_protocol  = $_SERVER['SERVER_PROTOCOL'];
	$status_message = self::get_http_status_message($this->http_status);
	header($http_protocol.' '.$status_message);
	
	parent::send_response($content_type, $encode_options);
	exit;
}

public function set_http_status($http_status) {
	if (empty($http_status)) {
		return;
	}
	
	$this->http_status = $http_status;
}

public function add_error($error=null, $friendly_message=null, $about_link=null) {
	if ($error instanceof error == false) {
		$error = new error($error, $friendly_message, $about_link);
	}
	
	$this->add_error_object($error);
}

public function fill_errors($errors) {
	foreach ($errors as $error) {
		$this->add_error_object($error);
	}
}

/**
 * [add_exception description]
 * @param [type] $exception        [description]
 * @param [type] $friendly_message [description]
 * @param [type] $about_link       [description]
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
	
	$this->add_error_object($new_error);
}

private function add_error_object(\alsvanzelf\jsonapi\error $error) {
	$error_response_part = $error->get_array();
	$error_http_status   = $error->get_http_status();
	
	$this->errors_collection[] = $error_response_part;
	$this->set_http_status($error_http_status);
}

public function add_included_resource(\alsvanzelf\jsonapi\resource $resource) {
	throw new \Exception('can not add included resources to errors, add them as meta data instead');
}

public function fill_included_resources($resources) {
	throw new \Exception('can not add included resources to errors, add them as meta data instead');
}

public static function get_http_status_message($status_code) {
	if (empty(self::$http_status_messages[$status_code])) {
		$status_code = 500;
	}
	
	return $status_code.' '.self::$http_status_messages[$status_code];
}

}
