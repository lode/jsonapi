<?php

namespace alsvanzelf\jsonapiTests\helpers;

use Psr\Http\Message\StreamInterface;

class TestableNonInterfaceStreamInterface implements StreamInterface {
	protected $document;
	
	public function __construct($document) {
		$this->document = $document;
	}
	
	/**
	 * StreamInterface
	 */
	
	public function getContents() {
		if ($this->document === null) {
			return '';
		}
		
		return (string) json_encode($this->document);
	}
	
	// not used in current implementation
	public function __toString() {
		return '';
	}
	
	public function close() {}
	
	public function detach() {
		return null;
	}
	
	public function getSize() {
		return null;
	}
	
	public function tell() {
		return 0;
	}
	
	public function eof() {
		return false;
	}
	
	public function isSeekable() {
		return false;
	}
	
	public function seek($offset, $whence = SEEK_SET) {}
	
	public function rewind() {}
	
	public function isWritable() {
		return false;
	}
	
	public function write($string) {
		return 0;
	}
	
	public function isReadable() {
		return false;
	}
	
	public function read($length) {
		return '';
	}
	
	public function getMetadata($key = null) {
		return null;
	}
}
