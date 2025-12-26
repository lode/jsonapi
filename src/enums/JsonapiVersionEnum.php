<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\enums;

enum JsonapiVersionEnum: string {
	case V_1_0  = '1.0';
	case V_1_1  = '1.1';
	case Latest = self::V_1_1->value;
}
