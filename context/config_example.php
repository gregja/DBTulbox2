<?php 

/*
 * Titre de l'application
 */
define ( 'NOM_APPLI', 'Company YYY' ); 
define ( 'TYP_MENU_APP', '*standard') ;
define ( 'MNU_APP_CONTEXT', '*IBMi') ;

/*
 * profil de connexion DB2
*/
$usr = 'XXXXXX';           // TODO : to personalize
$pwd = 'ZZZZZZZ';          // TODO : to personalize
$ip = 'xxx.xxx.xxx.xxx';   // TODO : to personalize

/*
 * Si la plateforme d'exécution est un IBMi alors on récupère son nom automatiquement,
 * dans le cas contraire, on définit manuellement la machine cible hébergeant la base DB2
 */
if (Misc::isIBMiPlatform ()) {
	$zend_server_type = 'ZS/i5' ; // environnement de type Zend Server sur i5

	define ( 'TYPE_ENVIR_EXE', 'IBMi' );
	define ( 'TYPE_ENVIR_APP', $ip );

} else {
	$zend_server_type = 'ZS/Win' ; // environnement de type Zend Server sur Windows
	/*
	 * dans le contexte Windows, les machines associées aux environnements sont définies en dur ci-dessous
	 * exemple de liste de machines : 'TEST', 'PREPROD', 'PROD', etc. 
	 */
	define ( 'TYPE_ENVIR_EXE', php_uname ( 's' ) );
	define ( 'TYPE_ENVIR_APP', $ip ); 
}

/*
 * liste des serveurs IBM i 
*/
$liste_servers = [];
$liste_servers[] = ['server' => $ip, 'lib' => ['MYPRECIOUS1', 'MYPRECIOUS2'] ];
$liste_servers[] = ['server' => 'rec.acmecompany.com', 'usr'=> 'XXX', 'pwd' => 'ZZZ', 'lib' => []];


if (TYPE_ENVIR_APP == $ip ) {
	/*
	* Liste de bibliothèques 
	*/
	define ( 'BIB_REF_DTA', 'XLIBREF1' ); // Bib de référence pour le stockage des "traces" notamment
	define ( 'BIB_REF_PGM', 'XLIBREF2' ); // Bib de référence où sont stockées les procécures stockées DB2
} else {
	// autre environnement (non défini pour l'instant)

}