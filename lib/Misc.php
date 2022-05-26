<?php
abstract class Misc {

	/**
	 * Renvoie "true" si le serveur est un IBM i, "false" dans le cas contraire
	 */
	public static function isIBMiPlatform () {
	
		return (php_uname('s') == 'OS400' || PHP_OS == "AIX" || PHP_OS == "OS400") ? true : false;
	
	}
	
	/**
	 * 
	 * @return number
	 */
	public static function getMicrotime() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return (( float ) $usec + ( float ) $sec);
	}

	/**
	 * 
	 * @param unknown_type $start
	 */
	public static function getTimeDiff($start) {

		$stop = self::getMicrotime() ;
		return round ( ($stop - $start) * 1000, 2 );
	
	}
	
	/**
	 * technique basique pour identifier si le navigateur est IE ou pas
	 * (nécessaire notamment pour régler des différence d'affichage entre IE et Firefox)
	 */
	public static function isNavigatorIE () {
		$is_ie = strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE');
		if ($is_ie == false) {
			return false ;
		} else {
			return true ;
		}
	}
	
	public static function getHeaderEncoding($encoding='ISO-8859-1') {
		header("Content-Type: text/html;charset=".$encoding);
	}
	
	/**
	 * fonction basique destinée à aider le développeur, et surtout destinée à évoluer
	 * (pourrait dans l'avenir alimenter un fichier de log différent de la log standard php)
	 * @param unknown_type $var
	 * @param boolean $affiche  (true = affiche à l'écran via un var_dump formaté)
	 */
	public static function assistDebug($var, $affiche=false) {
		// envoie systématique dans la log
		ob_start() ;
		var_dump($var);
		$dump = ob_get_clean() ;
		error_log($dump);
	
		// affichage dans le flux html si demandé
		if ($affiche) {
			echo '<pre>';
			var_dump($dump);
			echo '</pre>';
		}
	}
	
}