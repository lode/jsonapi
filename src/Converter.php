<?php

namespace alsvanzelf\jsonapi;

class Converter {
	/**
	 * @param  object $object
	 * @return array
	 */
	public static function objectToArray(object $object) {
		return get_object_vars($object);
	}
}
