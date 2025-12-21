<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\enums;

enum RelationshipTypeEnum {
	case ToOne;
	case ToMany;
}
