<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\enums;

/**
 * @internal
 */
enum ObjectContainerEnum: string {
	case Type          = 'type';
	case Id            = 'id';
	case Lid           = 'lid';
	case Attributes    = 'attributes';
	case Relationships = 'relationships';
}
