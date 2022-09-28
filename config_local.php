<?php 
// configuration DB2 locale 
$usr = 'XXXXXXXXX';     
$pwd = 'YYYYYYYYY';  
$ip = '*LOCAL';  

define ( 'TYPE_ENVIR_EXE', 'IBMi' );
define ( 'TYPE_ENVIR_APP', '*LOCAL' );

// Ajout paramétrage pour forcer la lecture des tables QADBFDEP, QADBKFLD, QADBXREF de QSYS
//  au travers de vues (dans le cas où le profil IBM i aurait des droits restreints sur QSYS)
// define ( 'SPECIFIC_VIEWS', true); 
// define ( 'SPECIFIC_LIB_VIEWS' , 'YOURVIEWLIB');  // définir votre propre bibliothèque de stockage des vues


// liste des serveurs accessibles depuis le stack PHP local
$liste_servers[] = ['server' => $ip, 'lib' => ['MYLIB'] ];
// profil non en place sur PRD01 
//$liste_servers[] = ['server' => 'dev.acme-company.com', 'usr'=> $usr, 'pwd' => $pwd, 'lib' => ['MYLIB2']];

