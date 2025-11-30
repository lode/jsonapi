<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\ExtensionMemberManager;
use alsvanzelf\jsonapi\interfaces\HasExtensionMembersInterface;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;

abstract class AbstractObject implements ObjectInterface, HasExtensionMembersInterface {
	use AtMemberManager, ExtensionMemberManager;
}
