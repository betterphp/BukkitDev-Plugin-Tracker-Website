<?php

include('core/init.inc.php');

if (!isset($_GET['id'])){
	die('E01: Improper request');
}

$project = project::get_by_id($_GET['id']);

if ($project === false){
	die('E02: No such project tracked');
}

$result = $project->get_file_stats((isset($_GET['days'])) ? intval($_GET['days']) : 0);

header('Content-Type: application/json');

echo json_encode($result, JSON_PRETTY_PRINT);

?>
