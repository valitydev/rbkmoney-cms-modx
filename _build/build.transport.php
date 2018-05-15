<?php

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

require_once 'build.config.php';

if (file_exists('build.model.php')) {
    require_once 'build.model.php';
}

$root = dirname(dirname(__FILE__)) . '/';
$sources = [
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'resolvers' => $root . '_build/resolvers/',
    'chunks' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/',
    'snippets' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/',
    'plugins' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/',
    'lexicon' => $root . 'core/components/' . PKG_NAME_LOWER . '/lexicon/',
    'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
    'pages' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/pages/',
    'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
    'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
    'payment_source_core_files' => [
        'components/' . PKG_NAME_LOWER_MINISHOP . '/custom/payment/rbkmoney.class.php',
    ],
];

require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/functions.php';

echo '<pre>';
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$modx->loadClass('transport.modPackageBuilder', '', false, true);

$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, PKG_NAMESPACE_PATH);

$modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package.');

$payment = $modx->newObject('msPayment', [
    'name' => PKG_NAME,
    'active' => 0,
    'class' => 'RBKmoneyPaymentHandler',
    'rank' => 100,
]);

$attributes = [
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => false
];
$vehicle = $builder->createVehicle($payment, $attributes);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers to payment...');

foreach ($sources['payment_source_core_files'] as $file) {
    $dir = dirname($file) . '/';
    $vehicle->resolve('file', [
        'source' => $root . 'core/' . $file,
        'target' => "return MODX_CORE_PATH . '{$dir}';"
    ]);
}

$builder->putVehicle($vehicle);
unset($file, $attributes);

if (defined('BUILD_MENU_UPDATE')) {
    $menus = include $sources['data'] . 'transport.menu.php';
    $attributes = [
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => BUILD_MENU_UPDATE,
        xPDOTransport::UNIQUE_KEY => 'text',
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
            'Action' => [
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => BUILD_ACTION_UPDATE,
                xPDOTransport::UNIQUE_KEY => [
                    'namespace',
                    'controller'
                ],
            ],
        ],
    ];
    if (is_array($menus)) {
        foreach ($menus as $menu) {
            $vehicle = $builder->createVehicle($menu, $attributes);
            $builder->putVehicle($vehicle);
            /* @var modMenu $menu */
            $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in menu "' . $menu->get('text') . '".');
        }
    } else {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in menu.');
    }
    unset($vehicle, $menus, $menu, $attributes);
}

$modx->log(xPDO::LOG_LEVEL_INFO, 'Created category.');

/* @var modCategory $category */
$category = $modx->newObject('modCategory');
$category->set('category', PKG_NAME);

$snippets = include $sources['data'] . 'transport.snippets.php';
if (!is_array($snippets)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in snippets.');
} else {
    $category->addMany($snippets);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($snippets) . ' snippets.');
}

$attr = [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Snippets' => [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ],
    ],
];
$vehicle = $builder->createVehicle($category, $attr);

$vehicle->resolve('file', [
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);
$vehicle->resolve('file', [
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
]);

foreach ($BUILD_RESOLVERS as $resolver) {
    if ($vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.' . $resolver . '.php'])) {
        $modx->log(modX::LOG_LEVEL_INFO, 'Added resolver "' . $resolver . '" to category.');
    } else {
        $modx->log(modX::LOG_LEVEL_INFO, 'Could not add resolver "' . $resolver . '" to category.');
    }
}

$builder->putVehicle($vehicle);

$builder->setPackageAttributes([
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
]);

$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');

$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Execution time: {$totalTime}\n");

echo '</pre>';
