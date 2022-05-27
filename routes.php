<?php
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

microFmw::get('/dbRoutineDisplay', function($app) {
    $app->set('schema', $app->form('schema'));
    $app->set('routine', $app->form('routine'));
    $app->render('dbRoutineDisplay');
}, $cnxdb, "document.querySelector(\"[data-tab='1']\").childNodes[0].click();"); // click sur le premier onglet


