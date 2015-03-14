<?php

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
require_once '../../config.php';

$helper = new auth_simplesaml_helper();
$metadata = $helper->get_metadata();

header('Content-Type: text/xml');
echo $metadata;
