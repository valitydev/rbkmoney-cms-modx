<?php

$snippets = [];

$tmp = [
    'RBKmoneyCallback',
    'RBKmoneyPayment',
    'RBKmoneyPaymentRecurrent',
];

foreach ($tmp as $name) {
    $snippet = $modx->newObject('modSnippet');
    $snippet->fromArray([
        'id' => 0,
        'name' => $name,
        'description' => '',
        'snippet' => getSnippetContent($sources['source_core'] . "/elements/snippets/$name.php"),
        'static' => BUILD_SNIPPET_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . "/elements/snippets/$name.php",
    ], '', true, true);

    if (file_exists($sources['build'] . "properties/properties.$name.php")) {
        $properties = include $sources['build'] . "properties/properties.$name.php";
        $snippet->setProperties($properties);
    }

    $snippets[] = $snippet;
}

unset($tmp, $properties);

return $snippets;