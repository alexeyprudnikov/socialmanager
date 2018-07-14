<?php

/**
 * Class UserFinder
 * @author: alexeyprudnikov
 */
class UserFinder extends Finder {
	/**
     *
     */
	public static function findByAuthData($name,$pass) {
		$self = new static();
		$query = 'SELECT * FROM tbl_users WHERE (s_UserName = "' . addslashes( $name ). '" OR s_Email = "' . addslashes( $name ). '") AND s_Password = "' . $pass . '" LIMIT 1';
		$result = $self->Database->select($query);
		return $self->loadCollection('User', $result)->getByIndex(0);
	}
}