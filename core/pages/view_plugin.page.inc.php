<?php

if (empty($_GET['slug']) || !preg_match('#^[a-z0-9_\-]+$#i', $_GET['slug'])){
	echo '<div class="msg error">Invalid slug.</div>';
	return;
}

$project = project::get_by_slug($_GET['slug']);

if ($project === false){
	echo '<div class="msg error">No such project.</div>';
	return;
}

?>
<div id="plugin-header">
	<h1 id="plugin-title"><a href="http://dev.bukkit.org/bukkit-plugins/<?php echo $project->get_slug(); ?>/" title="View on BukkitDev"><?php echo htmlentities($project->get_title()); ?></a></h1>
	<a href="embed_plugin.html?id=<?php echo $project->get_id(); ?>" title="Get embed code" id="embed-link"><img src="ext/img/embed_ico.png" alt="Embed" /></a>
</div>
<?php

$file_stats = $project->get_file_stats();

if (empty($file_stats)){
	echo '<div class="msg error">We don\'t have any data for this project yet, check back in a few days.</div>';
	return;
}

$file_download_data['cols'][] = array('id' => 'date', 'label' => 'Date', 'type' => 'date');

foreach ($file_stats as $file_number => &$info){
	$file_download_data['cols'][] = array('id' => 'file' . $file_number, 'label' => $info['title'], 'type' => 'number');
	
	if (isset($info['stats'])){
		foreach ($info['stats'] as $key => $stats){
			$download_stats['Date(' . (round($stats['start_time'] / 86400) * 86400000) . ')'][$info['title']] = $stats['downloads'];
		}
	}
}

if (empty($download_stats) || count($download_stats) < 2){
	echo '<div class="msg error">We don\'t have enough data for this project yet, check back tomorrow.</div>';
	return;
}

foreach ($download_stats as $date => $totals){
	$columns = array();
	$columns[] = array('v' => $date);
	
	foreach ($file_stats as $file_number => &$info){
		$columns[] = array('v' => (isset($totals[$info['title']])) ? $totals[$info['title']] : null);
	}
	
	$file_download_data['rows'][] = array('c' => $columns);
}

$total_download_data['cols'][] = array('id' => 'date', 'label' => 'Date', 'type' => 'date');
$total_download_data['cols'][] = array('id' => 'downloads', 'label' => 'Downloads', 'type' => 'number');

foreach ($download_stats as $date => $totals){
	$total_download_data['rows'][] = array('c' => array(array('v' => $date), array('v' => array_sum($totals))));
}

?>
<h2>Total Downloads</h2>
<div id="total_download_dashboard" class="line-chart">
	<div id="total_download_chart" class="chart"></div>
	<div id="total_download_control" class="control"></div>
</div>

<h2>File Downloads</h2>
<div id="file_download_dashboard" class="line-chart">
	<div id="file_download_chart" class="chart"></div>
	<div id="file_download_control" class="control legend"></div>
</div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load('visualization', '1.1', { packages: ['corechart', 'controls'] });
	
	google.setOnLoadCallback(function(){
		var totalData = new google.visualization.DataTable(<?php echo json_encode($total_download_data); ?>);
		var fileData = new google.visualization.DataTable(<?php echo json_encode($file_download_data); ?>);
		
		var totalDashboard = new google.visualization.Dashboard(document.getElementById('total_download_dashboard'));
		var fileDashboard = new google.visualization.Dashboard(document.getElementById('file_download_dashboard'));
		
		var totalControl = new google.visualization.ControlWrapper({
			controlType:	'ChartRangeFilter',
			containerId:	'total_download_control',
			options:		{
								filterColumnIndex:	0,
								ui: {
									chartType:		'LineChart',
									chartOptions:	{
														chartArea:	{ width:			'90%' },
														hAxis:		{ baselineColor:	'none' },
														vAxis:		{ minValue: 0 }
													},
									minRangeSize:	86400000
								}
							},
			state:			{
								range: {
									start: new Date((new Date()).getTime() - 2.62974e9),
									end: new Date()
								}
							}
		});
		
		var totalChart = new google.visualization.ChartWrapper({
			chartType:		'LineChart',
			containerId:	'total_download_chart',
			options:		{
								chartArea:		{ width: "90%", height: "90%" },
								hAxis:			{ slantedText: false },
								vAxis:			{ minValue: 0 },
								legend:			{ position: 'none' },
								titlePosition:	'none',
							},
		});
		
		totalDashboard.bind(totalControl, totalChart);
		totalDashboard.draw(totalData);
		
		var fileControl = new google.visualization.ControlWrapper({
			controlType:	'ChartRangeFilter',
			containerId:	'file_download_control',
			options:		{
								filterColumnIndex:	0,
								ui: {
									chartType:		'LineChart',
									chartOptions:	{
														chartArea:	{ width: '90%', height: '50%' },
														hAxis:		{ baselineColor: 'none' },
														vAxis:		{ minValue: 0 },
														legend:		{ position: 'bottom' }
													},
									minRangeSize:	86400000
								}
							},
			state:			{
								range: {
									start: new Date((new Date()).getTime() - 2.62974e9),
									end: new Date()
								}
							}
		});
		
		var fileChart = new google.visualization.ChartWrapper({
			chartType:		'LineChart',
			containerId:	'file_download_chart',
			options:		{
								chartArea:		{ width: "90%", height: "90%" },
								hAxis:			{ slantedText: false },
								vAxis:			{ minValue: 0 },
								legend:			{ position: 'none' },
								titlePosition:	'none',
							},
		});
		
		fileDashboard.bind(fileControl, fileChart);
		fileDashboard.draw(fileData);
	});
</script>
