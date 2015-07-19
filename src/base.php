<?php

namespace alsvanzelf\jsonapi;

class base {

/**
 * @deprecated
 * @see response::STATUS_*
 */
const STATUS_OK                    = response::STATUS_OK;
const STATUS_CREATED               = response::STATUS_CREATED;
const STATUS_NO_CONTENT            = response::STATUS_NO_CONTENT;
const STATUS_NOT_MODIFIED          = response::STATUS_NOT_MODIFIED;
const STATUS_TEMPORARY_REDIRECT    = response::STATUS_TEMPORARY_REDIRECT;
const STATUS_PERMANENT_REDIRECT    = response::STATUS_PERMANENT_REDIRECT;
const STATUS_BAD_REQUEST           = response::STATUS_BAD_REQUEST;
const STATUS_UNAUTHORIZED          = response::STATUS_UNAUTHORIZED;
const STATUS_FORBIDDEN             = response::STATUS_FORBIDDEN;
const STATUS_NOT_FOUND             = response::STATUS_NOT_FOUND;
const STATUS_METHOD_NOT_ALLOWED    = response::STATUS_METHOD_NOT_ALLOWED;
const STATUS_UNPROCESSABLE_ENTITY  = response::STATUS_UNPROCESSABLE_ENTITY;
const STATUS_INTERNAL_SERVER_ERROR = response::STATUS_INTERNAL_SERVER_ERROR;
const STATUS_SERVICE_UNAVAILABLE   = response::STATUS_SERVICE_UNAVAILABLE;

/**
 * @deprecated
 * @see response::CONTENT_TYPE_*
 */
const CONTENT_TYPE_OFFICIAL = response::CONTENT_TYPE_OFFICIAL;
const CONTENT_TYPE_DEBUG    = response::CONTENT_TYPE_DEBUG;

/**
 * @deprecated
 * @see response::ENCODE_*
 */
const ENCODE_DEFAULT = response::ENCODE_DEFAULT;
const ENCODE_DEBUG   = response::ENCODE_DEBUG;

/**
 * debug modus for non-production environments
 * 
 * this is automatically set based on the display_errors directive
 * it can be overridden by setting it to a boolean value
 * 
 * - encodes json with in pretty print (@see response::ENCODE_DEBUG) (*)
 * - makes browser display json instead of offering a file (@see response::CONTENT_TYPE_DEBUG) (*)
 * - outputs the error message for errors (@see error->get_array())
 * - outputs exception details for errors (@see errors->add_exception())
 * 
 * @note the effects marked with an asterisk (*) are automatically turned on ..
 *       .. when requested by a human developer (request with an accept header w/o json)
 */
public static $debug = null;

/**
 * base constructor for all objects
 * 
 * few things are arranged here:
 * - determines ::$debug based on the display_errors directive
 */
public function __construct() {
	// set debug mode based on display_errors
	if (is_null(self::$debug)) {
		self::$debug = (bool)ini_get('display_errors');
	}
}

/**
 * converting an object to an array
 * 
 * @param  object $object by default, its public properties are used
 *                        if it is a \alsvanzelf\jsonapi\resource, its ->get_array() is used
 * @return array
 */
protected static function convert_object_to_array($object) {
	if (is_object($object) == false) {
		throw new \Exception('can only convert objects');
	}
	
	if ($object instanceof \alsvanzelf\jsonapi\resource) {
		return $object->get_array();
	}
	
	return get_object_vars($object);
}

}
