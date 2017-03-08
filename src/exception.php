<?php

namespace alsvanzelf\jsonapi;

/**
 * custom exception for use in jsonapi projects
 * echo'ing the exception (or using ->send_response()) output a errors collection response
 * 
 * @note throwing the exception alone doesn't give you json output
 */

class exception extends \Exception {

/**
 * internal data containers
 */
protected $friendly_message;
protected $about_link;

/**
 * custom exception for usage by jsonapi projects
 * when echo'd, sends a jsonapi\errors response with the exception in it
 * 
 * can be thrown as a normal exception, optionally with two extra parameters
 * 
 * @param string    $message
 * @param integer   $code             optional, defaults to 500
 *                                    if using one of the predefined ones in jsonapi\response::STATUS_*
 *                                    sends out those as http status
 * @param Exception $previous
 * @param string    $friendly_message optional, which message to output to clients
 *                                    the exception $message is hidden unless base::$debug is true
 * @param string    $about_link       optional, a url to send clients to for more explanation
 *                                    i.e. a link to the api documentation
 */
public function __construct($message='', $code=0, $previous=null, $friendly_message=null, $about_link=null) {
	// exception is the only class not extending base
	new base();
	
	parent::__construct($message, $code, $previous);
	
	if ($friendly_message) {
		$this->set_friendly_message($friendly_message);
	}
	if ($about_link) {
		$this->set_about_link($about_link);
	}
}

/**
 * sets a main user facing message
 * 
 * @see error->set_friendly_message()
 */
public function set_friendly_message($friendly_message) {
	$this->friendly_message = $friendly_message;
}

/**
 * sets a link which can help in solving the problem
 * 
 * @see error->set_about_link()
 */
public function set_about_link($about_link) {
	$this->about_link = $about_link;
}

/**
 * sends out the json response of an jsonapi\errors object to the browser
 * 
 * @see errors->send_response()
 */
public function send_response($content_type=null, $encode_options=null, $response=null, $jsonp_callback=null) {
	$jsonapi = new errors($this, $this->friendly_message, $this->about_link);
	$jsonapi->send_response($content_type, $encode_options, $response, $jsonp_callback);
	exit; // sanity check
}

/**
 * alias for ->send_response()
 * 
 * @deprecated as this causes hard to debug issues ..
 *             .. when exceptions are called as a by-effect of this function
 * 
 * @return string empty for sake of correctness
 *                as ->send_response() already echo's the json and terminates script execution
 */
public function __toString() {
	if (base::$debug) {
		trigger_error('toString conversion of exception is deprecated, use ->send_response() instead', E_USER_DEPRECATED);
	}
	
	$this->send_response();
	return '';
}

}
