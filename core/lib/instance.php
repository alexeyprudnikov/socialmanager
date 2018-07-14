<?php
abstract class InstanceAbstract {

    protected static $instances = array();
	/**
     *
     */
    public static function getInstance() {
        $className = static::getClassName();
        if (!isset(self::$instances[$className]) || !(self::$instances[$className] instanceof $className)) {
            self::$instances[$className] = new $className();
        }
        return self::$instances[$className];
    }
    /**
     *
     */
    public static function removeInstance() {
        $className = static::getClassName();
        if (array_key_exists($className, self::$instances)) {
            unset(self::$instances[$className]);
        }
    }
    /**
     *
     */
    final protected static function getClassName() {
        return get_called_class();
    }

    protected function __construct() {}

    final protected function __clone() {}

    final protected function __sleep() {}

    final protected function __wakeup() {}
}

abstract class Instance extends InstanceAbstract {
    /**
     *
     */
    final public static function getInstance() {
        return parent::getInstance();
    }
    /**
     *
     */
    final public static function removeInstance() {
        parent::removeInstance();
    }
}
?>