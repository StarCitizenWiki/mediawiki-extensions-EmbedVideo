<?php
/**
 * Time saving script to convert an extension over to the new json format.
 * Use from the main hydra folder as your working directory.
 *
 * Usage:
 *   php master/tools/migrateI18n.php ExtensionName [ExtensionName...]
 *
 * 2014 Noah Manneschmidt
 */

if (PHP_SAPI !== 'cli') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// drop initial argument which is the name of this script
array_shift($argv);
var_dump($argv);
foreach ($argv as $extension) {
	if (!file_exists("$extension.i18n.php") || !file_exists("$extension.php")) {
		echo "Could not find main or i18n files for extension $extension\n";
		continue;
	}

	if (file_exists("i18n/")) {
		//system("cp -R extensions/{$extension}/* ~/Hydra/".strtolower($extension)."/extensions/{$extension}/");
		continue;
	}

	system("mkdir i18n; php ~/Sites/wikifarm/maintenance/generateJsonI18n.php ".__DIR__."/$extension.i18n.php i18n");

	// simple way to strip trailing closing php tag at the end of the file
	system("perl -p -i -e 's/\?>$//' $extension.php");

	// insert the extra rule at the end of the file
	$configLine = "\n\$wgMessagesDirs['$extension']					= \"{\$extDir}/i18n\";\n?>";
	$configLine = escapeshellarg($configLine);
	system("echo $configLine >> $extension.php");

	//system("cp -R extensions/{$extension}/* ~/Hydra/".strtolower($extension)."/extensions/{$extension}/");

	echo "Added \$wgMessagesDirs assignment line to the end of $extension.php\n\n";
}
