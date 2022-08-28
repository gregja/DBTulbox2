<?php 
// configuration DB2 locale 
$usr = 'XXXXXXXXX';     
$pwd = 'YYYYYYYYY';  
$ip = '*LOCAL';  

define ( 'TYPE_ENVIR_EXE', 'IBMi' );
define ( 'TYPE_ENVIR_APP', '*LOCAL' );

// liste des serveurs accessibles depuis le stack PHP local
$liste_servers[] = ['server' => $ip, 'lib' => ['GCFIC'] ];
// profil non en place sur PRD01 
//$liste_servers[] = ['server' => 'dev.acme-company.com', 'usr'=> $usr, 'pwd' => $pwd, 'lib' => ['GCFIC']];

