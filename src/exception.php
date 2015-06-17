<?php

namespace alsvanzelf\jsonapi;

/**
 * custom exception for use in jsonapi projects
 * echo'ing the exception (or using ->send_response()) output a errors collection response
 * 
 * @note throwing the exception alone doesn't give you json output
 */

class exception extends \Exception {

protected $friendly_message;
protected $about_link;

public function __construct($message='', $code=0, $previous=null, $friendly_message=null, $about_link=null) {
	parent::__construct($message, $code, $previous);
	
	$this->set_friendly_message($friendly_message);
	$this->set_about_link($about_link);
}

public function set_friendly_message($message) {
	$this->friendly_message = $message;
}

public function set_about_link($link) {
	$this->about_link = $link;
}

public function send_response($content_type=response::CONTENT_TYPE_OFFICIAL, $encode_options=448, $response=null) {
	$jsonapi = new errors($this, $this->friendly_message, $this->about_link);
	$jsonapi->send_response($content_type, $encode_options, $response);
	exit;
}

public function __toString() {
	$this->send_response();
	return '';
}

}
