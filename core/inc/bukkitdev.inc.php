<?php

/**
 * Represents the dev.bukkit.org site.
 */
class bukkitdev {
	
	/**
	 * Fetches information on a project
	 * 
	 * @param string $slug The URL slug of the project.
	 * @return array An array of information or false on failure.
	 */
	public static function fetch_project_info($slug){
		$url = "https://api.curseforge.com/servermods/projects?search={$slug}";
		$data = @file_get_contents($url, false, stream_context_create(array(
			'http' => array(
				'header'	=> implode("\r\n", array(
					'X-API-Key: ' . config::SERVERMODS_API_KEY,
					'User-Agent: PluginTracker/v0.1 (by wide_load)'
				)),
			)
		)));
		
		if ($data === false){
			return false;
		}
		
		foreach (json_decode($data, true) as $result){
			if ($result['slug'] == $slug){
				return array(
					'id'	=> intval($result['id']),
					'slug'	=> $result['slug'],
					'title'	=> $result['name']
				);
			}
		}
		
		return false;
	}
	
}

?>