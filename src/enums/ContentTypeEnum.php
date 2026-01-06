<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\enums;

enum ContentTypeEnum: string {
	case Official = 'application/vnd.api+json';
	case Debug    = 'application/json';
	case Jsonp    = 'application/javascript';
}
