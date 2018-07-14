<?php
class Database extends Instance {

	protected $connection;
	/**
     *
     */
	protected function __construct() {
		$this->connection = new SQLite3(SQLITE_DB_NAME);
	}
	/**
     *
     */
	public function getConnection() {
		return $this->connection;
	}
	/**
     *
     */
	public function initDatabase() {
		$sql = 'core/includes/dbinit.sql';
		if (!file_exists($sql)) {
			return false; 
		}
		$query = file_get_contents($sql);
		return $this->connection->exec($query);
	}
	/**
     *
     */
	public function isDatabaseEmpty() {
		$res = $this->connection->querySingle("SELECT count(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_stat%'");
		return ($res > 0 ? false : true);
	}
	/**
     *
     */
	public function select($query) {
		$result = array();
		$res = $this->connection->query($query);
		while($row = $res->fetchArray(SQLITE3_ASSOC)) {
			$result[] = $row;
        }
		return $result;
	}
	/**
     *
     */
	public function selectSingle($query) {
		$result = $this->connection->querySingle($query);
		return $result;
	}
}
?>