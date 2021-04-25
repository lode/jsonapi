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
	public function __toString() {}
	public function close() {}
	public function detach() {}
	public function getSize() {}
	public function tell() {}
	public function eof() {}
	public function isSeekable() {}
	public function seek($offset, $whence = SEEK_SET) {}
	public function rewind() {}
	public function isWritable() {}
	public function write($string) {}
	public function isReadable() {}
	public function read($length) {}
	public function getMetadata($key = null) {}
}
