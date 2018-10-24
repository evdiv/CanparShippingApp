<?php 

namespace Canpar;

class Database {

	private $host = DB_HOST;
	private $username = DB_USER;
	private $password = DB_PASSWORD;
	private $database = DB_NAME;
	private $dbconnect;


	private function connect() {
	
		if (empty($this->dbconnect)) {
			$mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
			
			if ($mysql->connect_errno) {
				die($mysql->connect_error);
			}
			$this->dbconnect = $mysql;
		}

		return $this->dbconnect;
	}


	public function query($query) {

		$db = $this->connect();
		$result = $db->query($query);

		return ($db->errno) ? false : $result;
	}

}