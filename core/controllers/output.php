<?php

/**
 * Class Output
 * @author: alexeyprudnikov
 */
class Output {
	protected $Database;
	protected $User;
	protected $Session;
	protected $Request;
	protected $Template;

	protected $channel;
	protected $channelCollection;

	public function __construct() {
		$this->Database = Database::getInstance();
		$this->User = Core::getUser();
		$this->Session = Session::getInstance();
		$this->Request = Request::getInstance();
		$this->Template = Template::getInstance();
	}
	/**
	*
	*/
	public function setPermanentOutput() {
		$Base = rtrim(Core::getBaseUrl(), '/') . '/';
		$this->Template->assign("Base", $Base );
		$this->Template->assign("User", $this->User );
	}

    /**
     *
     */
	public function start() {
		if($this->Request->get('logout') || Core::checkLocked()) {
			$this->logout();
		}
		$this->showStream();
	}

    /**
     *
     */
	protected function showStream() {

		$this->initChannel();

		if (!$this->channel) $this->initRedirect();

		$PostCollection = $this->channel->listPosts(null, 10);
		#print_r($PostCollection);
		//set channel data to collection posts
		$PostCollection->prepareCollection($this->channel);

		$this->Template->assign("PostCollection", $PostCollection);
		$this->Template->assign("ChannelCollection", $this->getChannelCollection());
		$this->Template->assign("ActiveChannel", $this->channel);
		$this->Template->setTemplate("tpl_main.php");
		$this->Template->addHeader(true);
		$this->Template->display();
	}

	public function initChannel() {
		$channelId  = $this->Request->get('cid') ? $this->Request->get('cid') : 0;
		$channel = $this->getChannelCollection()->getById((int)$channelId);
		if($channel->Id) $this->channel = $channel;
	}

	public function getChannelCollection() {
		if (!$this->channelCollection) {
			$filter = array('disabled'=>0, 'authorized'=>1);
			$this->channelCollection = ChannelFinder::find($filter);
		}
		return $this->channelCollection;
	}

	public function getChannel() {
		return $this->channel;
	}

	protected function initRedirect() {
		if (($channelId = $this->getChannelCollection()->getByIndex(0)->Id)) {
			header('location: '.$_SERVER['PHP_SELF'].'?cid='.$channelId);
		} else {
			$this->displayError(Core::getTranslation('es_wurden_keine_dateien_gefunden'), true);
			exit;
		}
	}

	/****** SYSTEM ******/

	/**
	 *
	 */
	public function init() {
		$this->Template->addHeader(true);
		$this->Template->setTemplate('tpl_init.php');
		if($this->Request->get('dbinit')) {
			$init = $this->Database->initDatabase();
			if($init) {
				$this->Template->assign('success', 'Init successful');
			} else {
				$this->Template->assign('error', 'Error by init');
			}
		}
		$this->Template->display();
	}
	/**
	 *
	 */
	public function login() {
        $this->Template->addHeader(true);
		$this->Template->setTemplate('tpl_login.php');
		if(Core::checkLocked()) {
			$this->Template->assign('locked', true);
			$this->Template->assign('error', Core::getTranslation('anmeldung_nicht_moeglich'));
		} else {
			if($this->Request->get('username') && $this->Request->get('password')) {
				$User = UserFinder::findByAuthData($this->Request->get('username'),md5($this->Request->get('password')));
				if ($User->Id) {
					$this->Session->set('User', $User);
					header( 'Location: ' . Core::getBaseUrl());
					exit;
				} else {
					$this->Template->assign('error', Core::getTranslation('anmeldung_fehlgeschlagen'));
				}
			}
		}
		$this->Template->display();
	}
	/**
	 *
	 */
	public function logout() {
		$this->Session->clear('User');
		header('Location: ' . Core::getBaseUrl());
		exit;
	}

	/**
	 * @param string $message
	 * @param bool $addHeader
	 */
	public function displayError($message = '', $addHeader = false) {
		$this->Template->assign('Message', $message);
		$this->Template->setTemplate('tpl_error.php');
		if($addHeader) {
			$this->Template->addHeader(true);	
		}
		$this->Template->display();
	}

	/**
	 * @param int $code
	 * @param string $message
	 */
	public function outputJson($code = 0, $message = '') {
		$response = array();
		$response['code'] = $code;
		$response['response'] = $message;
		header('Content-Type: application/json');
		echo json_encode($response);
	}
}