<?php

class Database {
	
	public $connection;

	/** Create a new database object */
	public function __construct() {
		$f3=Base::instance();
		
		extract($f3->get('db'));
		$this->connection=new DB\SQL(
		    'mysql:host='.$server.';port=3306;dbname='.$name,
		    $username,
		    $password
		);
	}

	/** Perform a direct database query */
	public function query($sql) {
		$result = $this->connection->exec($sql);
		return $result;
	}

}

?>
