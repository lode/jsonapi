<?php

use alsvanzelf\jsonapi\DataDocument;

require 'bootstrap_examples.php';

/**
 * use jsonapi to send out a status code
 */

$document = new DataDocument();
$document->setHttpStatusCode(201);
$document->sendResponse();
