<?php

if (array_key_exists('schema', $_GET) && array_key_exists('table', $_GET)) {
    $cnxdb = $this->getDB();
    $schema = Sanitize::blinderGet('schema');
    $table = Sanitize::blinderGet('table');
    $sql = DB2Tools::extractTableInfo();
    $data = $cnxdb->selectOne($sql, array($schema, $table));
    $system_schema = trim($data ['SYSTEM_TABLE_SCHEMA']);
    $system_table = trim($data ['SYSTEM_TABLE_NAME']);

    if (trim($data ['TABLE_TYPE']) == 'V') {
        $cet_objet_est_une_vue = true;
    } else {
        $cet_objet_est_une_vue = false;
    }

    /*
     * dans le cas où on a affaire à une table, affichage de la liste des indexs associés à cette table s'il y en a
     */
    echo '<br/><fieldset><legend><h6>Statistiques de l\'objet ' . $schema . '/' . $table . ' </h6></legend>' . PHP_EOL;

    /*
     * Si l'objet est une vue, alors on ne peut pas utiliser la table 
     * SYSTABLESTAT, par contre on peut faire un DSPFD pour extraire 
     * les informations essentielles (nbre de lignes totales, nbre de lignes
     * supprimées)
     */
    if ($cet_objet_est_une_vue) {
        list($cmd, $sql) = DB2Tools::extractDspfdMbrlist($system_table, $system_schema);
        $flag = $cnxdb->executeSysCommand($cmd);
        $mbrlist = $cnxdb->selectBlock($sql, array($system_schema, $system_table));
        if (is_array($mbrlist) && count($mbrlist) > 0) {
            echo '<br />(*) Données extraites via la commande système DSPFD <br />' . PHP_EOL;
            echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
            echo '<thead class="thead-dark">'.PHP_EOL;
            echo '<tr class="header-row">';
            echo '<th>Membre</th><th align="right">Nb rec.</th><th align="right">Nb rec.sup.</th>';
            echo '</tr>'.PHP_EOL ;
            echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
            foreach ($mbrlist as $key => $value) {
                if (!is_null($value) && trim($value) != '') { 
                    echo '<tr>' . PHP_EOL;
                    echo '<td>' . $value ['MLNAME'] . '</td>' . PHP_EOL;
                    echo '<td align="right">' . $value ['MLNRCD'] . '</td>' . PHP_EOL;
                    echo '<td align="right">' . $value ['MLNDTR'] . '</td>' . PHP_EOL;
                    echo '<tr>' . PHP_EOL;
                }
            }
            echo '</tbody>'.PHP_EOL;
            echo '</table>' . PHP_EOL;
        }
    }

    /*
     * Si l'objet n'est pas une vue, mais une table, alors on peut obtenir
     * des informations statistiques à partir de la table SYSTABLESTAT
     */
    if (!$cet_objet_est_une_vue) {
        echo '<br />(*) Données extraites via la table système SYSTABLESTAT<br />' . PHP_EOL;
        $sql = DB2Tools::extractTableStat($schema, $table);
        $tablestats = $cnxdb->selectOne($sql, array($schema, $table));
        echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
        echo '<thead class="thead-dark">'.PHP_EOL;
        echo '<tr class="header-row">';
        echo '<th>Statistique DB2</th><th>Valeur renvoyée par DB2</th>';
        echo '</tr>' . PHP_EOL;
        echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
        if (is_array($tablestats)) {
            foreach ($tablestats as $key => $value) {
                if (!is_null($value) && trim($value) != '') { 
                    echo '<tr>' . PHP_EOL;
                    echo '<td>&nbsp;' . trim($key) . '&nbsp;</td>' . PHP_EOL;
                    echo '<td>&nbsp;' . trim($value) . '&nbsp;</td>' . PHP_EOL;
                    echo '<tr>' . PHP_EOL;
                }
            }
        }
        echo '</tbody>'.PHP_EOL;
        echo '</table>' . PHP_EOL;
    }
    echo '<br><br>'.PHP_EOL ;
    if (!$cet_objet_est_une_vue) {
        // TODO : option à finaliser et à réactiver
        //    echo HtmlToolbox::genHtmlLink( 'dbextract_aff_statcol.php?schema=' . $schema . '&table=' . $table, 'Analyse statistique des colonnes' ) . '<br/>';
    }
    echo '<br>'.PHP_EOL ;
    //echo HtmlToolbox::genHtmlLink( 'dbextract_aff_statbib.php?schema=' . $schema , 'Statistiques de la bibliothèque' ) . '<br/>';
}
