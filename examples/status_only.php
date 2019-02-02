<?php

use alsvanzelf\jsonapi\DataDocument;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * use jsonapi to send out a status code
 */

$jsonapi = new DataDocument();
$jsonapi->setHttpStatusCode(201);
$jsonapi->sendResponse();
