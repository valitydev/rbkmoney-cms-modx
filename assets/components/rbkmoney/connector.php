<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('rbkmoney_core_path', null, $modx->getOption('core_path') . 'components/rbkmoney/');
require_once $corePath . 'model/rbkmoney.class.php';
$modx->rbkmoney = new RBKmoney($modx);

$modx->lexicon->load('rbkmoney:default');

/* handle request */
$path = $modx->getOption('processorsPath', $modx->rbkmoney->config, $corePath . 'processors/');
$modx->request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);