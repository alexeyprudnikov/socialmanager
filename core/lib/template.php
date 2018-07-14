<?php
class Template extends Instance {
	
	protected $placeholder;
	
	public $publicIncludePath = PUBLIC_INCLUDE_PATH;
	public $templatePath = TEMPLATE_PATH;
	public $TemplateName = 'tpl_main.php';
	protected $TemplateHeader = 'tpl_header.php';
	protected $TemplateFooter = 'tpl_footer.php';

	protected $AddHeader = false;
	
	/**
     *
     */
	protected function __construct() {
		$this->placeholder = array();
	}
	/**
     *
     */
	public function setTemplatePath($Path) {
		if (!dir($Path))
		{
			echo "Der angegebene Pfad wurde nicht gefunden." . $Path;
			return false;
		}
		$this->templatePath = $Path;
	}
	/**
     *
     */
	public function setTemplate($tpl) {
		$this->TemplateName = $tpl;
		return;

	}
	/**
     *
     */
	public function setHeader ($tpl) {
		if (empty($tpl)) return;
		$this->TemplateHeader = $tpl;
		return;
	}
	/**
     *
     */
	public function setFooter ($tpl) {
		if (empty($tpl)) return;
		$this->TemplateFooter = $tpl;
		return;
	}
	/**
     *
     */
	protected function getPathToTemplate( $tpl ) {
		if ( empty($tpl) ) $tpl = $this->TemplateName;
		
		$file = $this->templatePath . $tpl;			

		return (is_dir($file) OR !file_exists($file)) ? false : $file;
	}
	/**
     *
     */
	public function getTemplateContent($tpl) {
		return $this->getPathToTemplate( $tpl );

	}
	/**
     *
     */
	public function showPlaceHolder() {
		var_dump($this->placeholder);
	}
	/**
     *
     */
	public function getPlaceholder($plh) {
		$_plh = strtoupper($plh);

		if (strtolower($plh) == 'content') $plh = $_plh;
		if (array_key_exists($_plh, $this->placeholder))
		{
			return $this->placeholder[$_plh];
		} else if (array_key_exists($plh, $this->placeholder))
		{
			return $this->placeholder[$plh];
		} else return false;
	}
	/**
     *
     */
	public function assign($Key, $Value) {
		if (strtolower($Key) == 'content') $Key = $Key;
		$Key = trim( $Key );
		$this->placeholder[$Key] = $Value;
	}
	/** ------------------------------------------------------------------------------------------------------
	 * prepare template from text
	 *
	 * @param string $tpl - filename OR html-code of template
	 * @param string/mixed $name - entweder der Variablenen name oder ein assoc array
	 * @param null $value
	 *
	 * @return string-html
	 */
	public function rendernText( $tpl, $name = null, $value = null) {		
		ob_start();
		if (is_array($name)) {
			$placeholders = $name;
			
		} else {
			$placeholders = array();
			$placeholders[$name] = $value;
		}
		extract($placeholders);
		
		try {
			eval(" ?>" . $tpl . "<?php ");
			
		} catch (Exception $e) {
		
			return $e->getMessage();
		}
		
		return ob_get_clean();
	}
	/** ------------------------------------------------------------------------------------------------------
	 * prepare template from file
	 *
	 * @param string $tpl - filename OR html-code of template
	 * @param string/mixed $name - entweder der Variablenen name oder ein assoc array
	 * @param null $value
	 *
	 * @return string-html
	 */
	public function rendern( $tpl, $name = null, $value = null) {
		if ( empty($tpl) ) 
			return 'Templatename wurde nicht gefunden.';
		if ( !($file = $this->getPathToTemplate( $tpl )) ) 
			return 'Template-Datei existiert nicht.';
		
		
		ob_start();
		if (is_array($name)) {
			$placeholders = $name;
			
		} else {
			$placeholders = array();
			$placeholders[$name] = $value;
		}
		extract($placeholders);
		try {
			include $file;
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		return ob_get_clean();
	}
	/** ------------------------------------------------------------------------------------------------------
	 *	prepare and OUTPUT the template
	 * @param string $tpl - filename of template
	 *
	 * @return void
	 **/
	public function display($tpl = '', $return = false) {

		if ($tpl !== '') $this->TemplateName = $tpl;

		$tpl = $this->rendern( $this->TemplateName, $this->placeholder );

		if ($this->AddHeader)// den header und den Footer dazu laden
		{
			$tpl = $this->rendern($this->TemplateHeader, $this->placeholder) . $tpl . $this->rendern($this->TemplateFooter, $this->placeholder);	
		
		} else {		
			@header("Cache-Control: no-store, no-cache, must-revalidate");
			@header("Cache-Control: post-check=0, pre-check=0", false);
			@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		}
		
		if ($return) return $tpl;
		else echo $tpl;
	}	
	/** ------------------------------------------------------------------------------------------------------
	 * gibt alle platzhalter die sich im temüplate befinden aus
	 */
	public function printPlaceHolder() {
		print_r($this->placeholder);
	}

	/** ------------------------------------------------------------------------------------------------------
	 * 
	 */
	public function addHeader($stat = true) {
		$this->AddHeader = $stat;
	}

	public function removeHeader() {
		$this->AddHeader = false;
	}
	/**
     *
     */
	public function outputJSON($code, $message, $response = "") {
		header('Content-type: text/json');
		header("Cache-Control: no-store, no-cache, must-revalidate");   
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past


		if ($response && $response instanceof Collection || $response && $response instanceof Element) {
			//$response = $response->toJson();
			//$response = $response->toArray();
			// ob es funkt???
			$response = serialize($response);
		}

		$result = array(
			'code' => $code,
			'message' => $message,
			'response' => $response
		);

		echo json_encode($result);
		return;
	}
	/**
     *
     */
	public function printPublicIncludePath() {
		echo $this->publicIncludePath;
	}
	/**
     *
     */
	public function printHash() {
		// check common.css change date and update hash
		$file = PUBLIC_INCLUDE_PATH.'css/common.css';
		if (is_file($file)) {
			$changeDate = filemtime($file);
			echo substr( md5($changeDate), 0, 8 );
		} else {
			return;
		}
	}
	/**
     *
     */
	public function isAjax() {
		return !$this->AddHeader;
	}
}

?>