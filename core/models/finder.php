<?php

/**
 * Class Finder
 * @author: alexeyprudnikov
 */
class Finder {
	protected $Database;
	protected function __construct() {
		$this->Database = Database::getInstance();
	}
	/**
     *
     */
	protected function loadCollection($class = null, $data = array()) {
		if (is_null($class)) return false;
		
		$classCollection = $class.'Collection';
		if (!class_exists($classCollection)) return false;
		
		$temp = new $classCollection();
		
		if(empty($data)) return $temp;

		foreach($data as $row) {
			$temp->add($this->loadElement($class, $row) );
		}
		
		return $temp;
	}
	/**
     *
     */
	protected function loadElement($class = null, $data = array()) {
		if (is_null($class) || !class_exists($class)) return false;

		return new $class($data);
	}
}