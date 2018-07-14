<?php
/**
 * Created by PhpStorm.
 * User: alexeyprudnikov
 * Date: 09.06.17
 * Time: 13:42
 */
class Utility {
	/**
	 * @param $bytes
	 * @param int $decimals
	 * @return mixed
	 */
	public static function showHumanFilesize($bytes, $decimals = 2) {
		$sz = 'kMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		$formatted = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) .' '. @$sz[$factor-1] . 'B';
		return str_replace('.', ',', $formatted);
	}

	/**
	 * @param array $files
	 * @return bool
	 */
	public static function zipFiles($files = array()) {

		$archiveFile = 'download_'.md5(microtime()).'.zip';
		$zip = new ZipArchive();
		if($zip->open($archiveFile, ZIPARCHIVE::CREATE) !== TRUE) {
			trigger_error( "Could not create ".$archiveFile, E_ERROR );
			return false;
		}

		foreach ($files as $file) {
			if(!isset($file['presetPath'])) continue;
			$localname = isset($file['originalName']) ? $file['originalName'] : $file['presetPath'];
			if(file_exists($file['presetPath']) && !$zip->addFile($file['presetPath'], $localname)) {
				trigger_error( "error archiving ".$file['presetPath']." in ".$archiveFile, E_ERROR );
				return false;
			}
		}

		$zip->close();

		// download the zipped file
		self::pushFile($archiveFile, '', 'application/zip');
	}

	public static function pushFile($file = '', $name = '', $type = '') {
		if(empty($file) || empty($type)) return false;
		$name = empty($name) ? $file : $name;
		header('Content-Type: '.$type);
		header('Content-disposition: attachment; filename='.$name);
		header('Content-Length: ' . filesize($file));
		ob_clean();
		ob_end_flush();
		readfile($file);
		exit;
	}
}