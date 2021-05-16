<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\ExtensionMemberManager;

/**
 * using ExtensionMemberManager to make it non-trait to test against it
 */
class TestableNonTraitExtensionMemberManager {
	use ExtensionMemberManager;
}
