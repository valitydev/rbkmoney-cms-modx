<?php

use src\Helpers\Logger;

include "{$_SERVER['DOCUMENT_ROOT']}/core/components/rbkmoney/src/autoload.php";

$logger = new Logger();
$logger->downloadLog();