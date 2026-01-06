<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\enums;

/**
 * @internal
 */
enum ObjectContainerEnum {
	case Type;
	case Id;
	case Lid;
	case Attributes;
	case Relationships;
}
