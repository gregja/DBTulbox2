<?php 
/*
 * Config pour une base DB2 Express C
*/
if (Misc::isIBMiPlatform ()) {
	die('Contexte d\'utilisation incorrect') ;
} 

/*
 * profil de connexion DB2
 */
$usr = 'db2admin'; // TODO : to personalize
$pwd = 'db2admin'; // TODO : to personalize

/*
 * Titre de l'application
 */
define ( 'NOM_APPLI', 'DB2 Admin Local' );
define ( 'TYP_APPLI', 'mylab') ;
define ( 'CONTEXTE_MNU_APP', '*local') ;
/*
 * liste bibliothèques par environnement
 */
$liste_bibs = array ();

$zend_server_type = 'ZS/Win' ; // environnement de type Zend Server sur Windows

define ( 'TYPE_ENVIR_EXE', php_uname ( 's' ) );
define ( 'TYPE_ENVIR_APP', '' );
define ( 'BIB_REF_DTA', '' ); // Bib de référence pour le stockage des "traces" notamment
define ( 'BIB_REF_PGM', '' ); // Bib de référence où sont stockées les procécures stockées
