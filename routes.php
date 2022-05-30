<?php
/**
 * Returns JS code to select automatically the first tab of the page
 * @return string JS code
 */
function selectFirstTab() {
    return "document.querySelector(\"[data-tab='1']\").childNodes[0].click();";
}

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
}, $cnxdb, selectFirstTab());

microFmw::get('/dbRoutineDisplay', function($app) {
    $app->set('schema', $app->form('schema'));
    $app->set('routine', $app->form('routine'));
    $app->render('dbRoutineDisplay');
}, $cnxdb, selectFirstTab()); 

microFmw::get('/dbCompSimple', function($app) {
    $app->render('dbCompSimple');
}, $cnxdb, selectFirstTab()); 

/*
microFmw::post('/dbCompSimple', function($app) {
    $app->render('dbCompSimple');
}, $cnxdb, selectFirstTab()); 
*/
