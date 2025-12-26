<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\enums;

enum DocumentLevelEnum {
	case Root;
	case Jsonapi;
	case Resource;
}
