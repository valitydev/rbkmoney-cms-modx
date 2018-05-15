<?php

$snippets = [];

$tmp = [
    'RBKmoneyCallback' => 'RBKmoneyCallback',
    'RBKmoneyPayment' => 'RBKmoneyPayment',
    'RBKmoneyPaymentRecurrent' => 'RBKmoneyPaymentRecurrent',
];

foreach ($tmp as $k => $v) {
	/* @avr modSnippet $snippet */
	$snippet = $modx->newObject('modSnippet');
	$snippet->fromArray([
		'id' => 0,
		'name' => $k,
		'description' => '',
		'snippet' => getSnippetContent($sources['source_core']."/elements/snippets/$v.php"),
		'static' => BUILD_SNIPPET_STATIC,
		'source' => 1,
		'static_file' => 'core/components/'.PKG_NAME_LOWER."/elements/snippets/$v.php",
	], '', true, true);

    if (file_exists($sources['build'] . "properties/properties.$v.php")) {
        $properties = include $sources['build'] . "properties/properties.$v.php";
        $snippet->setProperties($properties);
    }

	$snippets[] = $snippet;
}

unset($tmp, $properties);

return $snippets;