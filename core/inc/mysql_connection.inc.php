<?php

class mysql_connection {
	
	private static $connection;
	
	public static function get_connection(){
		if (!isset(self::$connection)){
			self::$connection = new mysqli(config::DATABASE_HOST, config::DATABASE_USER, config::DATABASE_PASSWORD, config::DATABASE_NAME);
		}
		
		return self::$connection;
	}
	
}

?>
