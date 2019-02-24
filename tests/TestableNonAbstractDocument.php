<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\Document;

/**
 * extending Document to make it non-abstract to test against it
 * 
 * the abstract declaration is to make sure to create valid jsonapi output
 * as it needs at least one of `data`, `meta` or `errors`
 */
class TestableNonAbstractDocument extends Document {}
