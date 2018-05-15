<?php

/**
 * @param $filename
 *
 * @return string
 */
function getSnippetContent($filename) {
	$file = file_get_contents($filename);

    return trim(str_replace(['<?php', '?>'], '', $file));
}


/**
 * Recursive directory remove
 *
 * @param $dir
 */
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);

		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . "/" . $object) == "dir") {
					rrmdir($dir . "/" . $object);
				}
				else {
					unlink($dir . "/" . $object);
				}
			}
		}

		reset($objects);
		rmdir($dir);
	}
}