<?php
class Request extends Instance {
	/**
     *
     */
	protected function getType() {
		switch($_SERVER['REQUEST_METHOD']) {
			case 'GET':
			default:
				$type = $_GET;
				break;
			case 'POST':
				$type = $_POST;
				break;
		}
		return $type;
	}
	/**
     *
     */
	public function get($key) {
		$type = $this->getType();
		if (isset($type[$key])) {
			return $type[$key];
		}
		return false;
	}
	/**
     *
     */
	public function getAll() {
		return $this->getType();
	}
}
?>