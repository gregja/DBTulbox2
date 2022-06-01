<?php
$currentScript = 'dbCompSimple'; 

$liste_servers = $this->getServers();

$bib_a_analyser1 = Sanitize::blinderGet('bib_a_analyser1', '', 'strtoupper');
$bib_a_analyser2 = Sanitize::blinderGet('bib_a_analyser2', '', 'strtoupper');
$serveur1 = Sanitize::blinderGet('serveur1', 'int');
$serveur2 = Sanitize::blinderGet('serveur2', 'int');

$anomalies = array();
if (count($_GET) > 0) {
    if ($bib_a_analyser1 != '' && $bib_a_analyser2 == '') {
        $anomalies [] = 'Bibliothèque serveur 2 obligatoire';
    }
    if ($bib_a_analyser1 == '' && $bib_a_analyser2 != '') {
        $anomalies [] = 'Bibliothèque serveur 1 obligatoire';
    }
    if ($serveur1 == $serveur2 && $bib_a_analyser1 == $bib_a_analyser2) {
       // $anomalies [] = 'Incohérence sur les serveurs sélectionnés';
    }
}
if (count($anomalies) > 0) {
    echo '<fieldset><legend>Liste des anomalies</legend><font color = "red">';
    foreach ($anomalies as $anomalie) {
        echo $anomalie . '<br/>';
    }
    echo '</font></fieldset><br/>';
}

if ($serveur1 == '' && $serveur2 == '') {
    $serveur1 = 0; 
    $serveur2 = 0;
}

$serverFilter = function () use ($liste_servers) {
    $liste = [];
    foreach($liste_servers as $key=>$value) {
        $liste[] = $value['server'];
    }
    return $liste;
} ;

?>
<fieldset><legend>Recherche des écarts entre 2 bases de données DB2 (comparaison "simplifiée")</legend>
    <p>
        Attention, cette option de comparaison est dite "simplifiée" car elle n'effectue une recherche<br>
        que sur les noms de tables, sans chercher à comparer la structure des tables concordantes.<br>
    </p>
    <form id="extraction" name="extraction" method="get" action="">
        <div class="form-group row">    
            <label for="bib_a_analyser1" class="col-sm-5 col-form-label">Saisissez la bibliothèque du serveur 1 (obligatoire) :</label> 
            <div class="col-sm-3">
            <input type="text" name="bib_a_analyser1" id="bib_a_analyser1" size="20" class="form-control"
                value="<?php echo $bib_a_analyser1; ?>" required />
            </div>
            <div class="col-sm-3">
                <img src="images/clear_left.png" id="bib_a_analyser1_clear" onclick="$('#bib_a_analyser1').val('');" alt="clear" />	
            </div>
            </div>
        </div>
        <div class="form-group row">    
            <label for="bib_a_analyser2" class="col-sm-5 col-form-label">Saisissez la bibliothèque du serveur 2 (obligatoire) :</label> 
            <div class="col-sm-3">
            <input type="text" name="bib_a_analyser2" id="bib_a_analyser2" size="20" class="form-control"
                value="<?php echo $bib_a_analyser2; ?>" required />
            </div>
            <div class="col-sm-3">    
                <img src="images/clear_left.png" id="bib_a_analyser2_clear" onclick="$('#bib_a_analyser2').val('');" alt="clear"/>	
            </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="type_objet" class="col-sm-5 col-form-label">Serveur 1 </label>
            <div class="col-sm-3">
                <?php echo GenForm::inputSelect('serveur1', $serverFilter(), $serveur1) ;?>
            </div>
        </div>	
        <div class="form-group row">
            <label for="type_objet" class="col-sm-5 col-form-label">Serveur 2 </label>
            <div class="col-sm-3">
                <?php echo GenForm::inputSelect('serveur2', $serverFilter(), $serveur2) ;?>
            </div>
        </div>	
        <input type="submit" value="valider" name="crud_valid" id="crud_valid" class="btn btn-primary" /> 
    <!--    <input type="submit" value="export_csv" name="crud_export_csv" id="crud_export_csv" /> -->
    </form>
</fieldset>

<br />
<?php
if ($bib_a_analyser1 != '' && count($anomalies) == 0) {
    $cnx_db01 = $this->getDB();

    $sql = DB2Tools::extractDb2ObjectsFromLib(true, false, false);

    $sql_count = 'with tmp as ('.$sql.') select count(*) as comptage from tmp' ;

    $stmt_ref1 = null;
    $tmp_comptage1 = null;

    if ($liste_servers[$serveur1]['server'] == TYPE_ENVIR_APP) {
        $stmt_ref1 = $cnx_db01->getStatement($sql, array($bib_a_analyser1));
        $tmp_comptage1 = $cnx_db01->selectOne($sql_count, array($bib_a_analyser1)) ;
    } else {
        $tmp_cnx1 = $liste_servers[$serveur1]['cnx'];
        $stmt_ref1 = $tmp_cnx1->getStatement($sql, array($bib_a_analyser1));
        $tmp_comptage1 = $tmp_cnx1->selectOne($sql_count, array($bib_a_analyser1)) ;
    }

    $stmt_ref2 = null;
    $tmp_comptage2 = null;

    if ($liste_servers[$serveur2]['server'] == TYPE_ENVIR_APP) {
        $stmt_ref2 = $cnx_db01->getStatement($sql, array($bib_a_analyser2));
        $tmp_comptage2 = $cnx_db01->selectOne($sql_count, array($bib_a_analyser2)) ;      
    } else {
        $tmp_cnx2 = $liste_servers[$serveur2]['cnx'];
        $stmt_ref2 = $tmp_cnx2->getStatement($sql, array($bib_a_analyser2));
        $tmp_comptage2 = $tmp_cnx2->selectOne($sql_count, array($bib_a_analyser2)) ;
    }

    $comptage1 = isset($tmp_comptage1['COMPTAGE'])?$tmp_comptage1['COMPTAGE']:0 ;
    $comptage2 = isset($tmp_comptage2['COMPTAGE'])?$tmp_comptage2['COMPTAGE']:0 ;

    echo '<br/>Liste des écarts constatés entre les bibliothèques : ' . $bib_a_analyser1 . ' et ' . $bib_a_analyser2 . '<br/><br/>';

    $tab_ecarts = array();
    $tab_vide = array();

    // Tant que les 2 bibliothèques ont des objets à comparer
    $data_ref1 = DBWrapper::getFetchAssoc($stmt_ref1);
    $data_ref2 = DBWrapper::getFetchAssoc($stmt_ref2);
    if ($data_ref1 != false || $data_ref2 != false) {
        $end_maching = false;

        while (!$end_maching) {
            if ($data_ref1 != false && $data_ref2 == false) {
                // Fin de donnés sur REF2, mais pas sur REF1, alors on boucle sur REF1 jusqu'à ce que mort s'en suive
                $tab_ecarts [] = array('cause' => 'non trouvé sur Serveur 2', 'ref1' => $data_ref1, 'ref2' => $tab_vide);
                $data_ref1 = DBWrapper::getFetchAssoc($stmt_ref1);
            }
            if ($data_ref2 != false && $data_ref1 == false) {
                // Fin de donnés sur REF1, mais pas sur REF2, alors on boucle sur REF2 jusqu'à ce que mort s'en suive
                $tab_ecarts [] = array('cause' => 'non trouvé sur Serveur 1', 'ref1' => $tab_vide, 'ref2' => $data_ref2);
                $data_ref2 = DBWrapper::getFetchAssoc($stmt_ref2);
            }

            if ($data_ref1 != false && $data_ref2 != false) {
                // Comparaison des données contenues dans REF1 et REF2
                $result = array_diff($data_ref1, $data_ref2);
                if (count($result) == 0) {
                    // pas de différences trouvées, alors les 2 curseurs avancent de concert
                    $data_ref1 = DBWrapper::getFetchAssoc($stmt_ref1);
                    $data_ref2 = DBWrapper::getFetchAssoc($stmt_ref2);
                } else {
                    /*
                     * La fonction de comparaison strcmp() de PHP ne renvoie pas la même valeur que la comparaison
                     * de chaîne du point de vue de DB2, du coup c'est la fonction DB2 qui l'emporte ici
                     */
                    // $comparaison = strcmp ( trim($data_ref1 ['DBXLFI']),  trim($data_ref2 ['DBXLFI']) );
                    $sql_comp = DB2Tools::compareStrings($data_ref1 ['DBXLFI'], $data_ref2 ['DBXLFI']);
                    $data_comp = DBWrapper::selectOne($cnx_db01, $sql_comp);
                    $comparaison = $data_comp ['RESULT'];

                    if ($comparaison == 0) {
                        $tab_ecarts [] = array('cause' => 'format en écart', 'ref1' => $data_ref1, 'ref2' => $data_ref2);
                        // différences apparues sur les même objets, alors les 2 curseurs avancent de concert
                        $data_ref1 = DBWrapper::getFetchAssoc($stmt_ref1);
                        $data_ref2 = DBWrapper::getFetchAssoc($stmt_ref2);
                    } else {
                        if ($comparaison > 0) {
                            $tab_ecarts [] = array('cause' => 'non trouvé sur REF1', 'ref1' => $tab_vide, 'ref2' => $data_ref2);
                            // $data_ref1 est en avance sur $data_ref2, alors on fait "avancer" $data_ref2
                            $data_ref2 = DBWrapper::getFetchAssoc($stmt_ref2);
                        } else {
                            $tab_ecarts [] = array('cause' => 'non trouvé sur REF2', 'ref1' => $data_ref1, 'ref2' => $tab_vide);
                            // $data_ref2 est en avance sur $data_ref1, alors on fait "avancer" $data_ref1
                            $data_ref1 = DBWrapper::getFetchAssoc($stmt_ref1);
                        }
                    }
                }
            }

            if ($data_ref1 == false && $data_ref2 == false) {
                // fin de la comparaison
                $end_maching = true;
            }
        }
    }

    $entete_tableau = array('N°', 'Nom table REF1', 'Nom table REF2', 'Cause');

    if (array_key_exists('crud_export_csv', $_GET)) {
        ob_clean();
        ob_start();

        ExportOffice::csv('extract_comp_bib');

        $entete_tableau [1] = 'Nom table pour ' . $bib_a_analyser1;
        $entete_tableau [7] = 'Nom table pour ' . $bib_a_analyser2;

        $entete_tmp = array();
        foreach ($entete_tableau as $col) {
            $entete_tmp [] = '"' . $col . '"';
        }
        echo implode(';', $entete_tmp) . PHP_EOL;
        foreach ($tab_ecarts as $key => $value) {
            if (isset($value ['ref1'] ['DBXLFI'])) {
                $ref1 = trim($value ['ref1'] ['DBXLFI']);
            } else {
                $ref1 = '';
            }
            if (isset($value ['ref2'] ['DBXLFI'])) {
                $ref2 = trim($value ['ref2'] ['DBXLFI']);
            } else {
                $ref2 = '';
            }
            echo $key . ';';
            echo '"' . $ref1 . '";';
            echo '"' . $ref2 . '";';
            echo '"' . $value ['cause'] . '";' . PHP_EOL;
        }

        ob_end_flush();
        exit();
    } else {

        echo 'Nombre d\'objets sur référentiel1 : ' . $comptage1 . '<br/>'; 
        echo 'Nombre d\'objets sur référentiel2 : ' . $comptage2 . '<br/>';
        echo 'Nombre d\'écarts comptabilisés    : ' . count($tab_ecarts) . '<br/>';

        echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
        echo '<thead class="thead-dark">'.PHP_EOL;
        echo '<tr>';
        echo '<th>&nbsp;</th>';
        echo '<th scope="col">REF1 = ' . $bib_a_analyser1 . ' (serveur1)</th>';
        echo '<th scope="col">REF2 = ' . $bib_a_analyser2 . ' (serveur2)</th>';
        echo '<th>&nbsp;</th>';
        echo '</tr>';
        echo '<tr>';
        $entete = '';
        foreach ($entete_tableau as $col) {
            $entete .= '<th>' . $col . '</th>';
        }
        echo $entete;
        echo '</tr>';
        echo '</thead>'.PHP_EOL;
        echo '<tbody>'.PHP_EOL;        
        foreach ($tab_ecarts as $key => $value) {
            echo '<tr>';
            if (isset($value ['ref1'] ['DBXLFI'])) {
                $ref1 = trim($value ['ref1'] ['DBXLFI']);
            } else {
                $ref1 = '&nbsp;';
            }
            if (isset($value ['ref2'] ['DBXLFI'])) {
                $ref2 = trim($value ['ref2'] ['DBXLFI']);
            } else {
                $ref2 = '&nbsp;';
            }

            echo '<td>' . $key . '</td>';
            echo '<td>' . $ref1 . '</td>';
            echo '<td>' . $ref2 . '</td>';
            echo '<td>' . $value ['cause'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
