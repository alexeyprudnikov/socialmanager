<?php

/**
 * Class Core
 * @author: alexeyprudnikov
 */
class Core extends Instance {
	protected $User;
	protected $Rights;
	protected static $ControllerPool = array();
	protected $lockFile = "site.lock";

	protected static $AvailableLanguages = array('de' => 'Deutsch', 'en' => 'English');
	protected static $defaultLanguage = "de";
	protected $SystemLanguageCode;
	protected $Dictionary;

    protected static $TypeStrMap = array(
        CHANNEL_TYPE_YOUTUBE => 'youtube',
        CHANNEL_TYPE_FACEBOOK => 'facebook',
        CHANNEL_TYPE_TWITTER => 'twitter',
        CHANNEL_TYPE_VIMEO => 'vimeo',
    );
	/**
     *
     */
	protected function __construct() {
		$this->setLanguage(Request::getInstance()->get('lang'));
		$this->loadDictionary();
		$this->loadUser();
	}
	/**
     *
     */
	public function start() {
		$outputController = $this->loadController('Output');
		if (is_null($outputController)) return;
		
		$outputController->setPermanentOutput();
		
		if (Database::getInstance()->isDatabaseEmpty()) {
			$outputController->init();
			return;
		}
		if (!$this->User->Id) {
			$outputController->login();
		} else {
			$outputController->start();
		}
	}
	/**
     *
     */
	public function loadController($ControllerName) {
		if ($ControllerName == 'Core') {
			return $this;
		}
		if (!isset(self::$ControllerPool[$ControllerName])) {
			self::$ControllerPool[$ControllerName] = class_exists($ControllerName) ? new $ControllerName() : null;
		}
		return self::$ControllerPool[$ControllerName];
	}
	
/****************** inits ******************/

	/**
	 * @param string $ln
	 */
	public function setLanguage($ln = '') {
		if(empty($ln)) {
			if(!Session::getInstance()->get('Language')) {
				Session::getInstance()->set('Language', self::$defaultLanguage);
			}
			$this->SystemLanguageCode = Session::getInstance()->get('Language');
		} else {
			$ln = strtolower($ln);
			if (!in_array($ln, array_keys(self::$AvailableLanguages)))
				$ln = self::$defaultLanguage;

			Session::getInstance()->set('Language', $ln);
			$this->SystemLanguageCode = $ln;
		}
	}

	/**
	 *
	 */
	protected function loadDictionary() {
		$jsonFile = 'translates.json';
		if(!file_exists($jsonFile))  return;

		$data = file_get_contents($jsonFile);
		if(empty($data)) return;

		$langObject = json_decode($data);
		$this->Dictionary = $langObject;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public static function getTranslation($key = '') {
		$lang = self::getInstance()->getSystemLanguage();

		if(empty($key))	return 'KEY::undefined';
		$key = strtolower($key);

		$Dictionary = self::getInstance()->getDictionary();

		if(!isset($Dictionary) || !isset($Dictionary->$key) || !isset($Dictionary->$key->$lang)) return 'KEY::'.$key;

		return $Dictionary->$key->$lang;

	}

	/**
	*
	*/
	private function loadUser() {
		#Session::getInstance()->clear('User');
		$User = Session::getInstance()->get('User');
		if($User && $User->Id) {
			$this->User = $User;
		} else {
			$this->User = User::getEmptyInstance();	
		}
	}
	
/****************** getters ******************/	
	
	/**
	*
	*/
	public static function getUser() {
		return self::getInstance()->User;
	}

	public function getSystemLanguage() {
		return self::getInstance()->SystemLanguageCode;
	}

	/**
	 *
	 */
	public function getDictionary() {
		return self::getInstance()->Dictionary;
	}

	public static function getAvailableLanguages() {
		return self::$AvailableLanguages;
	}

	public static function getDefaultLanguage() {
		return self::$defaultLanguage;
	}

    /**
     * return as Youtube - big first letter
     * @param $type
     *
     * @return string
     */
	public static function getChannelTypeString($type) {
	    if(empty($type) || !array_key_exists($type, self::$TypeStrMap)) return '';
	    return ucfirst(strtolower(self::$TypeStrMap[$type]));
    }

    /**
     * proxy für ausgehende Links, zB Social/Youtube hinter dem VPN
     * @return bool
     */
    public static function getProxy() {
        if (defined("PROXY_SERVER")) return PROXY_SERVER;
        return false;
    }
	
/****************** routing ******************/	
	
	/**
	*
	*/
	public static function getBaseUrl() {
		if(defined('BASE_URL')) return BASE_URL;
		$Protocol = (self::getInstance()->isSSL()) ? 'https://' : 'http://';
		$subfolder = (dirname($_SERVER['SCRIPT_NAME']) !== "/") ? dirname( $_SERVER['SCRIPT_NAME'] ) : '';
        return $Protocol . $_SERVER['HTTP_HOST'].$subfolder;
	}
	/**
     *
     */
	protected function isSSL () {
		if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off') || ((!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))) {
			return true;
		}
		return false;
	}

	public static function checkLocked() {
		if(is_file(self::getInstance()->lockFile)) {
			return true;
		} else {
			return false;
		}
	}
}