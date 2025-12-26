<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\AtMemberManager;

/**
 * using AtMemberManager to make it non-trait to test against it
 */
class TestableNonTraitAtMemberManager {
	use AtMemberManager;
}
