<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\AtMembers;

/**
 * using AtMembers to make it non-trait to test against it
 */
class TestableNonTraitAtMembers {
	use AtMembers;
}
