<?php

/**
 * Class Collection
 * @author: alexeyprudnikov
 */
abstract class Collection implements Iterator {
	protected $Elements;
	protected $ElementCounter;
	
	protected $ObjectClassName;
	/**
     *
     */
	public function __construct() {	
		$this->Elements= array();
		$this->ElementCounter = 0;
	}
	/**
     *
     */
	public function getByIndex($index) {
        if(!isset($this->Elements[$index])) {
			$ObjectClassName = $this->ObjectClassName;
			return $ObjectClassName::getEmptyInstance();
		}
        return $this->Elements[$index];
    }
    public function getByKey($Key, $Value) {
		foreach ($this->Elements as $element) {
			if($element->$Key == $Value) {
				return $element;
			}
		}
		$ObjectClassName = $this->ObjectClassName;
		return $ObjectClassName::getEmptyInstance();
	}
	public function getById($Id) {
		return $this->getByKey('Id', $Id);
	}
	/**
     *
     */
	public function add($Element) {
		$this->Elements[]=$Element;
		$this->ElementCounter++;
	}
	public function addFirst($Element) {
		array_unshift($this->Elements, $Element);
		$this->ElementCounter++;
	}
	/**
     *
     */
	public function getCount() {
		return $this->ElementCounter;
	}

	/**
	 *
	 */
	public function getIds() {
		$List = array();
		foreach ($this->Elements as $Element) {
			$List[] = $Element->Id;
		}
		return $List;
	}

	/**
	 *
	 */
	public function getIdsAsString($Seperator =",") {
		$List = $this->getIds();
		return implode( $Seperator , $List );
	}
	
	public function revers() {
		$this->Elements = array_reverse( $this->Elements );
	}
	
	//--------------------------------------------------------------------------------------------------
	// iterator override
	//--------------------------------------------------------------------------------------------------
	public function rewind()
	{
		if (!isset($this->Elements)){return false;}
		reset($this->Elements);
	}
	
	public function current()
	{
		if (!isset($this->Elements)){return false;}
		$var = current($this->Elements);
		return $var;
	}
	
	public function key()
	{
		if (!isset($this->Elements)){return false;}
		$var = key($this->Elements);
		return $var;
	}
	
	public function next()
	{
		$var = next($this->Elements);
		return $var;
	}
	public function prev()
	{
		$var = prev($this->Elements);
		return $var;
	}
	public function valid() {
		$var = $this->current() !== false;
		return $var;
	}
	
	public function mixElements()
	{
		shuffle($this->Elements);
		return true;	
	}
}