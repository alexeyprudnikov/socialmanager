<?php

/**
 * Class Element
 * @author: alexeyprudnikov
 */
abstract class Element
{
    protected $data = array();

    public function __construct($data = array()) {
        if (!empty($data)) {
            $this->setValuesFromArray($data);
            return;
        }
    }

    public function __set($key, $value) {
        $this->data[$key] = $value;
    }

    public function __get($key) {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }

    public static function getEmptyInstance() {
        return new static();
    }

    public function setValuesFromArray($array) {
        foreach ($array as $key => $value) {
            $TempNameParts = explode('_', $key);
            if (strlen($TempNameParts[0]) === 1) {
                $TempNameParts = array_slice($TempNameParts, 1);
            }

            if (empty($TempNameParts[0])) {
                continue;
            }
            $key = ucfirst($TempNameParts[0]);
            $this->$key = $value;
        }
    }
}