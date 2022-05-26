<?php
microFmw::get('/', function($app) {
    $app->render('home');
}, $cnxdb);

microFmw::get('/dbTablesExtract', function($app) {
    $app->set('nom_base', $app->form('nom_base'));
    $app->set('nom_table', $app->form('nom_table'));
    $app->render('dbTablesExtract');
}, $cnxdb);

microFmw::get('/dbTableDisplay', function($app) {
    $app->set('schema', $app->form('schema'));
    $app->set('table', $app->form('table'));
    $app->render('dbTableDisplay');
}, $cnxdb, "document.querySelector(\"[data-tab='1']\").childNodes[0].click();"); // click sur le premier onglet

microFmw::get('/dbCompSimple', function($app) {
    $app->set('nom_base', $app->form('nom_base'));
    $app->set('nom_table', $app->form('nom_table'));
    $app->render('dbCompSimple');
}, $cnxdb);

