<?php
define ("SYSTEM_DEBUG", false);

if (SYSTEM_DEBUG) echo "<hr />Starting<hr />";


define ("SYSTEM", dirname(__FILE__));
define ("SYSTEM_CLASSES", SYSTEM.DIRECTORY_SEPARATOR."classes");
define ("SYSTEM_MAILER", SYSTEM.DIRECTORY_SEPARATOR."swift-mailer".DIRECTORY_SEPARATOR."lib");
define ("APP_PATH", str_replace(DIRECTORY_SEPARATOR."system".DIRECTORY_SEPARATOR."core", "", SYSTEM).DIRECTORY_SEPARATOR."source");
define ("CONTROLLERS", APP_PATH.DIRECTORY_SEPARATOR."controllers");
define ("MODELS", APP_PATH.DIRECTORY_SEPARATOR."models");
define ("VIEWS", APP_PATH.DIRECTORY_SEPARATOR."views");
define ("UTILS", APP_PATH.DIRECTORY_SEPARATOR."utils");
define ("I18N", APP_PATH.DIRECTORY_SEPARATOR."i18n");
define ("CACHE_FILES", APP_PATH.DIRECTORY_SEPARATOR."cache");
define ("CONFIG_FILES", APP_PATH.DIRECTORY_SEPARATOR."config");
define ("VIEWS_SUFFIX", ".tpl.php");
define ("JS_FILES", "source/themes/js/");
define ("CSS_FILES", "source/themes/css/");
define ("IMG_FILES", "source/themes/img/");
?>
