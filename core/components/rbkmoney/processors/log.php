<?php

use src\Helpers\Logger;

$method = $_POST['method'];

$method(new Logger());

$corePath = $modx->getOption('rbkmoney_core_path', null, $modx->getOption('core_path') . 'components/rbkmoney/');

include $corePath . 'src/autoload.php';


function delete(Logger $logger)
{
    $logger->deleteLog();

    echo json_encode([
        'success' => true,
    ]);
}

function show(Logger $logger)
{
    echo json_encode([
        'success' => true,
        'message' => $logger->getLog(),
    ]);
}