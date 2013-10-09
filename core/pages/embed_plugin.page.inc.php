<?php

if (empty($_GET['id'])){
	echo '<div class="msg error">Invalid slug.</div>';
	return;
}

$project = project::get_by_id($_GET['id']);

if ($project === false){
	echo '<div class="msg error">No such project.</div>';
	return;
}

?>
<div id="plugin-header">
	<h1 id="plugin-title"><a href="http://dev.bukkit.org/bukkit-plugins/<?php echo $project->get_slug(); ?>/" title="View on BukkitDev"><?php echo htmlentities($project->get_title()); ?></a></h1>
</div>

<div>
	<img src="download_graph.php?id=<?php echo $project->get_id(); ?>" alt="downloads" />
</div>

<h2>Code</h2>
<div>
	<div class="embed-code">
		<label for="bbcode-code">BBCode</label>
		<input type="text" id="bbcode-code" readonly="readonly" value="<?php echo htmlentities('[url=http://bukkit.jacekk.co.uk/plugin_tracker/view_plugin.html?slug=' . $project->get_slug() . '][img]http://bukkit.jacekk.co.uk/plugin_tracker/download_graph.php?id=' . $project->get_id() . '[/img][/url]'); ?>" />
	</div>
	
	<div class="embed-code">
		<label for="markdown-code">Markdown</label>
		<input type="text" id="markdown-code" readonly="readonly" value="<?php echo htmlentities('[![downloads](http://bukkit.jacekk.co.uk/plugin_tracker/download_graph.php?id=' . $project->get_id() . ')](http://bukkit.jacekk.co.uk/plugin_tracker/view_plugin.html?slug=' . $project->get_slug() . ' "View detailed stats")'); ?>" />
	</div>
	
	<div class="embed-code">
		<label for="safehtml-code">Safe HTML</label>
		<input type="text" id="safehtml-code" readonly="readonly" value="<?php echo htmlentities('<a href="http://bukkit.jacekk.co.uk/plugin_tracker/view_plugin.html?slug=' . $project->get_slug() . '" title="View detailed stats"><img src="http://bukkit.jacekk.co.uk/plugin_tracker/download_graph.php?id=' . $project->get_id() . '" alt="downloads" /></a>'); ?>" />
	</div>
	
	<div class="embed-code">
		<label for="wikicreole-code">WikiCreole</label>
		<input type="text" id="wikicreole-code" readonly="readonly" value="<?php echo htmlentities('[[http://bukkit.jacekk.co.uk/plugin_tracker/view_plugin.html?slug=' . $project->get_slug() . '|{{http://bukkit.jacekk.co.uk/plugin_tracker/download_graph.php?id=' . $project->get_id() . '|View detailed stats}}]]'); ?>" />
	</div>
</div>
