<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\ManageHttpStatusCode;

/**
 * using ManageHttpStatusCode to make it non-trait to test against it
 */
class TestableNonTraitManageHttpStatusCode {
	use ManageHttpStatusCode;
}
