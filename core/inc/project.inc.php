<?php

/**
 * Represents a project on BukkitDev.
 */
class project {
	
	/**
	 * @var int The projects ID.
	 */
	private $id;
	
	/**
	 * @var string The URL slug.
	 */
	private $slug;
	
	/**
	 * @var string The most recent title.
	 */
	private $title;
	
	/**
	 * @var int The first time this project was seen.
	 */
	private $first_seen;
	
	public function __construct($id, $slug, $title, $first_seen){
		$this->id = $id;
		$this->slug = $slug;
		$this->title = $title;
		$this->first_seen = $first_seen;
	}
	
	/**
	 * Gets the internal ID of the project.
	 * 
	 * @return int The ID.
	 */
	public function get_id(){
		return $this->id;
	}
	
	/**
	 * Gets the URL slug of the project.
	 * 
	 * @return string The slug.
	 */
	public function get_slug(){
		return $this->slug;
	}
	
	/**
	 * Gets the most recent title of the project.
	 * 
	 * @return string The title.
	 */
	public function get_title(){
		return $this->title;
	}
	
	/**
	 * Fetches download stats on the files linked to this project.
	 * 
	 * @param int $days The number of days worth of data to process.
	 * @return array An array of file download stats.
	 */
	public function get_file_stats($days = 0){
		$mysql = mysql_connection::get_connection();
		
		$sql = "SELECT
					`files`.`file_number`,
					`files`.`file_title`,
					`files`.`file_name`,
					`file_stats`.`stat_downloads`,
					`file_stats`.`stat_time_collected`
				FROM `file_stats`
				INNER JOIN `files`
				ON `file_stats`.`file_id` = `files`.`file_id`
				WHERE `files`.`project_id` = ?";
		
		if ($days > 0){
			$sql .= ' AND `file_stats`.`stat_time_collected` > (UNIX_TIMESTAMP() - ' . ($days * 86400) . ')';
		}
		
		$sql .= ' ORDER BY `file_stats`.`stat_time_collected` ASC';
		
		$stmt = $mysql->prepare($sql);
		$stmt->bind_param('i', $this->id);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		
		$results = array();
		$files = array();
		
		while (($row = $result->fetch_assoc()) != null){
			if (!isset($files[$row['file_number']])){
				$files[$row['file_number']] = array(
					'id'		=> $row['file_number'],
					'title'		=> $row['file_title'],
					'name'		=> $row['file_name'],
				);
			}
			
			$results[$row['file_number']][] = array(
				'downloads'	=> $row['stat_downloads'],
				'time'		=> $row['stat_time_collected'],
			);
		}
		
		foreach ($results as $file_number => $data){
			$entries = count($data);
			
			for ($i = 1; $i < $entries; ++$i){
				$files[$file_number]['stats'][] = array(
					'downloads'		=> $data[$i]['downloads'] - $data[$i - 1]['downloads'],
					'start_time'	=> $data[$i - 1]['time'],
					'end_time'		=> $data[$i]['time'],
				);
			}
		}
		
		foreach ($files as $file_number => &$info){
			if (!empty($info['stats'])){
				foreach ($info['stats'] as &$stat){
					$stat['downloads'] /= max(round(($stat['end_time'] - $stat['start_time']) / 86400), 1);
				}
			}
		}
		
		return $files;
	}
	
	/**
	 * Fetches a project by a slug.
	 * 
	 * @param string $slug The slug
	 * @return project The project or false on failure.
	 */
	public static function get_by_slug($slug){
		$mysql = mysql_connection::get_connection();
		
		$sql = 'SELECT
					`project_id`,
					`project_title`,
					`project_first_seen`
				FROM `projects`
				WHERE `project_slug` = ?';
		
		$stmt = $mysql->prepare($sql);
		$stmt->bind_param('s', $slug);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		
		if ($result->num_rows != 1){
			$info = bukkitdev::fetch_project_info($slug);
			
			if ($info === false){
				return false;
			}
			
			$sql = "INSERT INTO `projects` (`project_id`, `project_slug`, `project_title`, `project_first_seen`)
					VALUES (?, ?, ?, UNIX_TIMESTAMP())
					ON DUPLICATE KEY UPDATE
						`project_slug` = VALUES(`project_slug`),
						`project_title` = VALUES(`project_title`)";
			
			$stmt = $mysql->prepare($sql);
			$stmt->bind_param('iss', $info['id'], $info['slug'], $info['title']);
			$stmt->execute();
			$stmt->close();
			
			return new self($info['id'], $info['slug'], $info['title'], time());
		}else{
			$row = $result->fetch_assoc();
			
			return new self(intval($row['project_id']), $slug, $row['project_title'], intval($row['project_first_seen']));
		}
	}
	
	/**
	 * Fetches a project by ID.
	 * 
	 * @param int $id The ID.
	 * @return project The project or failse on failure.
	 */
	public static function get_by_id($id){
		$mysql = mysql_connection::get_connection();
		
		$sql = 'SELECT
					`project_id`,
					`project_slug`,
					`project_title`,
					`project_first_seen`
				FROM `projects`
				WHERE `project_id` = ?';
		
		$stmt = $mysql->prepare($sql);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		
		if ($result->num_rows != 1){
			return false;
		}
		
		$row = $result->fetch_assoc();
		
		return new self(intval($row['project_id']), $row['project_slug'], $row['project_title'], intval($row['project_first_seen']));
	}
	
	/**
	 * Fetches the most popular projects based on the number of downloads a single file has received.
	 * 
	 * @param int $limit The number of projects to return.
	 * @return array An array of projects.
	 */
	public static function get_most_popular($limit = 10){
		$mysql = mysql_connection::get_connection();
		
		$sql = "SELECT
					`projects`.`project_id`,
					`projects`.`project_title`,
					`projects`.`project_slug`,
					`projects`.`project_first_seen`,
					SUM(`file_downloads`.`max_downloads`) AS `downloads`
				FROM `projects`
				INNER JOIN `files`
				ON `projects`.`project_id` = `files`.`project_id`
				INNER JOIN (
					SELECT
						`files`.`file_id`,
						MAX(`file_stats`.`stat_downloads`) AS `max_downloads`
					FROM `files`
					INNER JOIN `file_stats`
					ON `files`.`file_id` = `file_stats`.`file_id`
					GROUP BY `files`.`file_id`
				) AS `file_downloads`
				ON `files`.`file_id` = `file_downloads`.`file_id`
				GROUP BY `projects`.`project_id`
				ORDER BY `downloads` DESC
				LIMIT 0, ?";
		
		$stmt = $mysql->prepare($sql);
		$stmt->bind_param('i', $limit);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		
		$projects = array();
		
		while (($row = $result->fetch_assoc()) != null){
			$projects[] = new self(intval($row['project_id']), $row['project_slug'], $row['project_title'], intval($row['project_first_seen']));
		}
		
		return $projects;
	}
	
}

?>
