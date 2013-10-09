<?php

$core_path = dirname(__FILE__);

if (end(explode('/', current(get_included_files()))) == 'index.php'){
	if (!isset($_GET['page'])){
		header("{$_SERVER['SERVER_PROTOCOL']} 302 Found");
		header('Location: home.html');
		die();
	}
	
	$template_file_name = "{$_GET['page']}.page.inc.php";
	
	if (!in_array($template_file_name, scandir("{$core_path}/pages"))){
		header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
		header('Location: home.html');
		die();
	}
	
	$template_file = "{$core_path}/pages/{$template_file_name}";
}

include("{$core_path}/config.inc.php");

function plugin_tracker_autoload($class_name){
	$locations = array(
		"{$GLOBALS['core_path']}/inc",
//		"{$GLOBALS['core_path']}/lib",
	);
	
	foreach ($locations as &$location){
		if (file_exists("{$location}/{$class_name}.inc.php")){
			include_once("{$location}/{$class_name}.inc.php");
			return;
		}
	}
	
	die("Unable to find {$class_name}.inc.php <pre>" . print_r(debug_backtrace(), true) . '</pre>');
}

spl_autoload_register('plugin_tracker_autoload');

?>
