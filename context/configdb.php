<?php
if (! isset ( $cnxdb )) {
	
	$options = array ();
	$options['i5_naming'] = true ;
	$options['i5_libl'] = $liste_servers[0]['lib'];
	$options['DB2_ATTR_CASE'] = 'UPPER';	
    $options['CCSID'] = '1208';
	
	/*
	 * Si la plateforme est de type IBMi alors on se connecte à la base de données avec DB2_connect,
	 * sinon on se connecte à la base de données avec PDO
	 */
	if (Misc::isIBMiPlatform ()) {		
		/*
		 * Ouverture d'une connexion BD sur un serveur IBM i, avec DB2 Connect
		 */
		require_once '../lib/macaronDB/DB2/IBMi/DBWrapper.php';
		require_once '../lib/macaronDB/DB2/IBMi/DBConnex.php';
		require_once '../lib/macaronDB/DB2/IBMi/DBInstance.php';
		//$cnxdb = new DB2_IBMi_DBInstance(TYPE_ENVIR_APP, $usr, $pwd, $options ) ;
		$cnxdb = new DB2_IBMi_DBInstance(TYPE_ENVIR_APP, $usr, $pwd ) ;
		abstract class DBWrapper extends DB2_IBMi_DBWrapper {} ;		
	} else {
		/*
		* Ouverture d'une connexion BD sur un serveur Windows ou Linux, avec PDO
		*/
		require_once 'lib/macaronDB/PDO/DB2IBMi/DBWrapper.php';
		require_once 'lib/macaronDB/PDO/DB2IBMi/DBConnex.php';
		require_once 'lib/macaronDB/PDO/DB2IBMi/DBInstance.php';
		$cnxdb = new PDO_DB2IBMi_DBInstance(TYPE_ENVIR_APP, $usr, $pwd, $options ) ;
		abstract class DBWrapper extends PDO_DB2IBMi_DBWrapper {} ;	
	}
	
}

// Ouverture des connexions aux serveurs secondaires si définis
for ($i=0, $imax=count($liste_servers); $i<$imax; $i++) {
	$tmpsrv = $liste_servers[$i];
	if ($tmpsrv['server'] == TYPE_ENVIR_APP) {
		$liste_servers[$i]['cnx'] = $cnxdb;
	} else {
		$options['i5_libl'] = $tmpsrv['lib'];
		if (Misc::isIBMiPlatform ()) {		
			$liste_servers[$i]['cnx'] = new DB2_IBMi_DBInstance($tmpsrv['server'], $tmpsrv['usr'], $tmpsrv['pwd'], $options ) ;	
		} else {
			$liste_servers[$i]['cnx'] = new PDO_DB2IBMi_DBInstance($tmpsrv['server'], $tmpsrv['usr'], $tmpsrv['pwd'], $options ) ;
		}
	}
}


