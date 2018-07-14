<?php

/**
 * Class Channel
 * @author: alexeyprudnikov
 */
class Channel extends Element {

    protected $TypeStr;
    protected $Handler;
    protected $Languages;

    public function getTypeStr() { return $this->TypeStr; }
    public function setTypeStr($arg) { $this->TypeStr = $arg; }

    /**
    * @param array $items
    * @param string $nextPageIdentifier
    * @return PostCollection
    */
    protected function loadPostCollection($items = array(), $nextPageIdentifier = '') {
        $collection = new PostCollection();
        if(count($items) > 0) {
            foreach($items as $data) {
                $element = $this->loadPost($data);
                $collection->add($element);
            }
        }
        if(!empty($nextPageIdentifier)) $collection->setLoadMoreIdentifier($nextPageIdentifier);

        return $collection;

    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function loadPost($data = array()) {
        $className = $this->getTypeStr().'Post';
        $element = class_exists($className) ? new $className($data) : new Post($data);
        return $element;
    }

	/**
	 * @return bool
	 */
	public function initHandler(){ return false; }

	public function listPosts($loadMoreIdentifier, $limit){
		if (!$this->initHandler()) {
			#echo $this->Handler->getLastError();
			return new PostCollection();
		}
        list($items, $nextPageIdentifier) = $this->Handler->listPosts($loadMoreIdentifier, $limit);
		#echo $this->Handler->getLastError();
        // load collection
        $collection = $this->loadPostCollection($items, $nextPageIdentifier);
        return $collection;
	}

	public function updatePost($id, $data = array()) {
		if (!$id || empty($data) || !$this->initHandler()) {
			#echo $this->Handler->getLastError();
			return false;
		}
		$newItem = $this->Handler->updatePost($id, $data);
		#echo $this->Handler->getLastError();
		return $newItem;
	}

	public function deletePost($id) {
		if (!$id || !$this->initHandler()) {
			#echo $this->Handler->getLastError();
			return false;
		}
		$delete = $this->Handler->deletePost($id);
		#echo $this->Handler->getLastError();
		return $delete;
	}

	public function insertPost($data = array()) {
		if (empty($data) || !$this->initHandler()) {
			#echo $this->Handler->getLastError();
			return false;
		}
		$newItem = $this->Handler->insertPost($data);
		#echo $this->Handler->getLastError();
		return $newItem;
	}

	public function getLanguages() { return array(); }
}

class YoutubeChannel extends Channel {
    protected $ClientId;
    protected $ClientSecret;
    protected $AccessToken;

    public function __construct($data = array()) {
        parent::__construct($data);
        if($this->Options) $this->setAuthData($this->Options);
    }

    public function setAccessToken($arg){ $this->AccessToken = $arg; }

    public function setAuthData($arg){

        $options = empty($arg) ? array() : (is_array($arg) ? $arg : json_decode($arg, 1));

        if (array_key_exists('ClientId', $options)) {
            $this->ClientId = $options['ClientId'];
        }

        if (array_key_exists('ClientSecret', $options)) {
            $this->ClientSecret = $options['ClientSecret'];
        }

        if (array_key_exists('AccessToken', $options)) {
            $this->AccessToken = $options['AccessToken'];
        }
    }

    /**
     * @return array
     */
    public function getAuthData() {
        return array(
            'Id' => $this->Id,
            'ClientId' => $this->ClientId,
            'ClientSecret' => $this->ClientSecret,
            'AccessToken' => $this->AccessToken
        );
    }

    /**
     * @return bool
     */
    public function initHandler(){
        if(!$this->Handler) {
            $this->Handler = SocialMediaFactory::createYoutube($this->getAuthData());
        }
        return $this->Handler->init();
    }

    public function getLanguages() {
        if(!$this->Languages) {
            $this->Languages = $this->initHandler() ? $this->Handler->getLanguages() : array();
        }
        return $this->Languages;
    }
}