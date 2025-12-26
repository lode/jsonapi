<?php

declare(strict_types=1);

use alsvanzelf\jsonapi\MetaDocument;

require 'bootstrap_examples.php';

/**
 * use jsonapi to send out a status code
 */

$document = new MetaDocument();
$document->setHttpStatusCode(201);
$document->sendResponse();
