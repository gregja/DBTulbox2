<?php

/*
 * Titre de l'application
 */
define ( 'NOM_APPLI', 'ACME Productions' );
define ( 'TYP_MENU_APP', '*standard');
define ( 'MNU_APP_CONTEXT', '*IBMi');

/*
 * liste bibliothèques par environnement
*/
$liste_bibs = array ();

/*
* Paramètres user, password, adresse IP placés en dehors du projet
*/
require_once '../config_external.php';
/*
 * profil de connexion DB2
*/
/*
$usr = 'ROGERRABBIT';           // TODO : à personnaliser
$pwd = 'ONRASEGRATIS';          // TODO : à personnaliser
$ip = 'dev.acmecompany.com';    // TODO : à personnaliser

$liste_servers = [];
$liste_servers[] = ['server' => $ip, 'lib' => ['MYPRECIOUS1', 'MYPRECIOUS2'] ];
$liste_servers[] = ['server' => 'rec.acmecompany.com', 'usr'=> 'XXX', 'pwd' => 'ZZZ', 'lib' => []];
$liste_servers[] = ['server' => 'prd.acmecompany.com', 'usr'=> 'XXX', 'pwd' => 'ZZZ', 'lib' => []];
*/

/*
 * Si la plateforme d'exécution est un IBMi alors on récupère son nom automatiquement,
 * dans le cas contraire, on définit manuellement la machine cible hébergeant la base DB2
 */
if (Misc::isIBMiPlatform ()) {
	// environnement de type Zend Server sur IBM i
	define ( 'TYPE_ENVIR_EXE', 'IBMi' );
	define ( 'TYPE_ENVIR_APP', $ip );
} else {
	// stack PHP hors IBM i
	define ( 'TYPE_ENVIR_EXE', php_uname ( 's' ) );
	define ( 'TYPE_ENVIR_APP', $ip );
}

define('DEBUG_MODE', false);
define('LIMIT_MAX_LIG_SQL', 10); // pris en compte par FETCH FIRST x ROWS ONLY
define('MAX_LINES_BY_PAGE', 20); // nombre de lignes maxi par page

set_time_limit(600);

setlocale(LC_ALL, 'fr_FR');
setlocale(LC_COLLATE, 'fr_FR');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', DEBUG_MODE);
