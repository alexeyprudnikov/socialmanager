<?php
class Session extends Instance {
	/**
     *
     */
	protected function __construct() {
		session_start();
	}
	/**
     *
     */
	public function get($key) {
		if(!isset($_SESSION[$key])) return null;
		return unserialize($_SESSION[$key]);
	}
	/**
     *
     */
	public function set($key, $value) {
		$_SESSION[$key] = serialize($value);
	}
	/**
     *
     */
	public function clear($key) {
		  unset($_SESSION[$key]);
	}
}
?>