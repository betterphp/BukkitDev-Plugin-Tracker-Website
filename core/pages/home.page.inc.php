<form method="get" action="view_plugin.html" id="search-form">
	<div>
		<label for="slug">BukkitDev Slug</label>
		<input type="text" id="slug" name="slug" />
		<input type="submit" value="View Stats" />
	</div>
</form>
<ol>
	<?php
	
	foreach (project::get_most_popular(10) as $project){
		?>
		<li><a href="view_plugin.html?slug=<?php echo $project->get_slug(); ?>"><?php echo htmlentities($project->get_title()); ?></a></li>
		<?php
	}
	
	?>
</ol>
