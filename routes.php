<?php
require 'lib/jsFunctions.php';

microFmw::get('/', function($app) {
    $app->render('home');
}, $cnxdb);

microFmw::get('/dbTablesExtract', function($app) {
    $app->set('nom_base', $app->form('nom_base'));
    $app->set('nom_table', $app->form('nom_table'));
    $app->render('dbTablesExtract');
}, $cnxdb);

microFmw::get('/dbColumnSearch', function($app) {
    $app->render('dbColumnSearch');
}, $cnxdb);

microFmw::get('/dbRoutinesExtract', function($app) {
    $app->render('dbRoutinesExtract');
}, $cnxdb);

microFmw::get('/dbTableDisplay', function($app) {
    $app->set('schema', $app->form('schema'));
    $app->set('table', $app->form('table'));
    $app->render('dbTableDisplay');
}, $cnxdb, [selectFirstTab(), showDifferedTab()]);

microFmw::get('/dbRoutineDisplay', function($app) {
    $app->set('schema', $app->form('schema'));
    $app->set('routine', $app->form('routine'));
    $app->render('dbRoutineDisplay');
}, $cnxdb, [selectFirstTab(), showDifferedTab()]); 

microFmw::get('/dbCompSimple', function($app) {
    $app->render('dbCompSimple');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTableDisplaySource', function($app) {
    $app->renderAjax('dbextract_affichage_source');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTableDisplaySourceProc', function($app) {
    $app->renderAjax('dbextract_affichage_routinesrc');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTableDisplayIndexs', function($app) {
    $app->renderAjax('dbextract_affichage_indexs');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTableDisplayLocks', function($app) {
    $app->renderAjax('dbextract_affichage_objlck', true);
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTableDisplayObjUsed', function($app) {
    $app->renderAjax('dbextract_affichage_depview');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTableDisplayObjUsers', function($app) {
    $app->renderAjax('dbextract_affichage_depinv');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTableDisplayTriggers', function($app) {
    $app->renderAjax('dbextract_affichage_triggers');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbRoutineDisplayObjUsed', function($app) {
    $app->renderAjax('dbextract_affichage_depproc');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbRoutineDisplayObjUsers', function($app) {
    $app->renderAjax('dbextract_affichage_depinvproc');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTriggerDisplay', function($app) {
    $app->set('schema', $app->form('schema'));
    $app->set('trigger', $app->form('trigger'));
    $app->render('dbTriggerDisplay');
}, $cnxdb, [selectFirstTab(), showDifferedTab()]); 

microFmw::get('/dbTriggerDisplaySource', function($app) {
    $app->renderAjax('dbextract_affichage_trigsrc');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

microFmw::get('/dbTriggerDisplayObjUsed', function($app) {
    $app->renderAjax('dbextract_affichage_deptrig');
}, $cnxdb, [], ['servers'=>$liste_servers]); 

