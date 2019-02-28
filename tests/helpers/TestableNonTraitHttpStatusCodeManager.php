<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\HttpStatusCodeManager;

/**
 * using HttpStatusCodeManager to make it non-trait to test against it
 */
class TestableNonTraitHttpStatusCodeManager {
	use HttpStatusCodeManager;
}
