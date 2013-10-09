<?php

/**
 * Holds a referenced to the MySQL server connection.
 */
class mysql_connection {
	
	private static $connection;
	
	/**
	 * Gets or creates a connection to the mysql server.
	 */
	public static function get_connection(){
		if (!isset(self::$connection)){
			self::$connection = new mysqli(config::DATABASE_HOST, config::DATABASE_USER, config::DATABASE_PASSWORD, config::DATABASE_NAME);
		}
		
		return self::$connection;
	}
	
}

?>
