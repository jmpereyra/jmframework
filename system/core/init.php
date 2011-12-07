<?php
defined("SYSTEM") or die("Can't execute directly");

if (SYSTEM_DEBUG) echo "Loading site config files.<hr />";
$dir = opendir(CONFIG_FILES);
while ($config = readdir($dir)) {
	if (preg_match("/^([A-Za-z])(.*)\.php$/", $config)) {
		if (SYSTEM_DEBUG) echo "->&nbsp;&nbsp;&nbsp;&nbsp;{$config}<br />";
		require_once CONFIG_FILES.DIRECTORY_SEPARATOR."{$config}";
	}
}
if (SYSTEM_DEBUG) echo "<hr />";
if (SYSTEM_DEBUG) echo "Loading database framework.<hr />";
require_once SYSTEM.DIRECTORY_SEPARATOR."database".DIRECTORY_SEPARATOR."ConnectionManager.php";

if (SYSTEM_DEBUG) echo "Loading MVC framework core classes.<br />";
$dir = opendir(SYSTEM_CLASSES);
while ($class = readdir($dir)) {
	if (preg_match("/^([A-Za-z])(.*)\.php$/", $class)) {
		if (SYSTEM_DEBUG) echo "->&nbsp;&nbsp;&nbsp;&nbsp;{$class}<br />";
		require_once SYSTEM_CLASSES.DIRECTORY_SEPARATOR."{$class}";
	}
}
if (SYSTEM_DEBUG) echo "<hr />";
?>
