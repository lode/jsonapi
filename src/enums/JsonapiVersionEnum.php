<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\enums;

enum JsonapiVersionEnum: string {
	case V_1_0  = '1.0';
	case V_1_1  = '1.1';
	
	/**
	 * @internal for public use {@see self::latest()}
	 * 
	 * this value is used as a default value for JsonapiObject's constructor
	 * since (enum) functions can't be used as default argument values
	 */
	case Latest = 'latest';
	
	public static function latest(): self {
		return self::V_1_1;
	}
}
