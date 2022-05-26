<?php
// TODO : en cours de modification 
$currentScript = 'dbCompSimple';
/*
require_once ('../inc/config.php');
require_once ('../inc/configdb.php');
require_once ('../inc/configdb02.php');
require_once ('../inc/configdb03.php');
*/
// temporairement, je duplique la connexion 1 vers les 2 autres
// TODO : étendre Bones pour transmettre plusieurs connecteurs
$cnx_db01 = $this->getDB();
$cnx_db02 = $this->getDB();
$cnx_db03 = $this->getDB();

$bib_a_analyser1 = Sanitize::blinderPost('bib_a_analyser1', '', 'strtoupper');
$bib_a_analyser2 = Sanitize::blinderPost('bib_a_analyser2', '', 'strtoupper');
$serveur1 = Sanitize::blinderPost('serveur1', '', 'strtoupper');
$serveur2 = Sanitize::blinderPost('serveur2', '', 'strtoupper');
$table_used_ref = Sanitize::blinderPost('table_used_ref', '', 'strtoupper');

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
    $serveur1 = TYPE_ENVIR_APP; // TYPE_ENVIR_APP02;
    $serveur2 = TYPE_ENVIR_APP;
}
?>
<fieldset><legend>Recherche des écarts entre 2 bases de données DB2 (comparaison "simplifiée")</legend>
    <p>
        Attention, cette option de comparaison est dite "simplifiée" car elle n'effectue une recherche<br>
        que sur les noms de tables, sans chercher à comparer la structure des tables concordantes.<br>
    </p>
    <form id="extraction" name="extraction" method="get" action="">
        <p><label for="bib_a_analyser1">Saisissez la bibliothèque du serveur 1
                (obligatoire) :</label> <input type="text" name="bib_a_analyser1" id="bib_a_analyser1" size="20"
                                           value="<?php echo $bib_a_analyser1; ?>" required />
            <img src="images/clear_left.png" id="bib_a_analyser1_clear" border="0" onclick="$('#bib_a_analyser1').val('');" alt="clear" />	
        </p>
        <p><label for="bib_a_analyser2">Saisissez la bibliothèque du serveur 2
                (obligatoire) :</label> <input type="text" name="bib_a_analyser2" id="bib_a_analyser2" size="20"
                                           value="<?php echo $bib_a_analyser2; ?>" required />
            <img src="images/clear_left.png" id="bib_a_analyser2_clear" border="0" onclick="$('#bib_a_analyser2').val('');" alt="clear"/>	
        </p>
    <!--    <p><label for="table_used_ref">Sélectionner uniquement les objets DB2 utilisés dans les procédures stockées :</label> 
            <input type="checkbox" name="table_used_ref" id="table_used_ref" value="ON" <?php echo $table_used_ref == 'ON' ? ' checked="checked" ' : ''; ?> />
        </p> -->
        <fieldset><legend>Serveur 1</legend> 
            <label for="serveur1-test"><input id="serveur1-test" name="serveur1" value="<?php echo TYPE_ENVIR_APP03; ?>" type="radio" <?php echo $serveur1 == TYPE_ENVIR_APP03 ? ' checked="checked" ' : ''; ?>/><?php echo TYPE_ENVIR_APP03; ?></label>
            <label for="serveur1-preprod"><input id="serveur1-preprod" name="serveur1" value="<?php echo TYPE_ENVIR_APP02; ?>" type="radio" <?php echo $serveur1 == TYPE_ENVIR_APP02 ? ' checked="checked" ' : ''; ?>/><?php echo TYPE_ENVIR_APP02; ?></label>
            <label for="serveur1-prod"><input id="serveur1-prod" name="serveur1" value="<?php echo TYPE_ENVIR_APP; ?>" type="radio" <?php echo $serveur1 == TYPE_ENVIR_APP ? ' checked="checked" ' : ''; ?>/><?php echo TYPE_ENVIR_APP; ?></label>
        </fieldset>
        <fieldset><legend>Serveur 2</legend> 
            <label for="serveur2-test"><input id="serveur2-test" name="serveur2" value="<?php echo TYPE_ENVIR_APP03; ?>" type="radio" <?php echo $serveur2 == TYPE_ENVIR_APP03 ? ' checked="checked" ' : ''; ?>/><?php echo TYPE_ENVIR_APP03; ?></label>
            <label for="serveur2-preprod"><input id="serveur2-preprod" name="serveur2" value="<?php echo TYPE_ENVIR_APP02; ?>" type="radio" <?php echo $serveur2 == TYPE_ENVIR_APP02 ? ' checked="checked" ' : ''; ?>/><?php echo TYPE_ENVIR_APP02; ?></label>
            <label for="serveur2-prod"><input id="serveur2-prod" name="serveur2" value="<?php echo TYPE_ENVIR_APP; ?>" type="radio" <?php echo $serveur2 == TYPE_ENVIR_APP ? ' checked="checked" ' : ''; ?>/><?php echo TYPE_ENVIR_APP; ?></label>
        </fieldset>
        <input type="submit" value="valider" name="crud_valid" id="crud_valid" /> 
    <!--    <input type="submit" value="export_csv" name="crud_export_csv" id="crud_export_csv" /> -->
    </form>
</fieldset>

<br />
<?php
if ($bib_a_analyser1 != '' && count($anomalies) == 0) {
    /*
     * si demandé, on ne retient que les tables et vues utilisées dans les
     * procédures stockées servant à alimenter l'infocentre
     */
    if ($table_used_ref == 'ON') {
        $ref_croisee = true;
    } else {
        $ref_croisee = false;
    }
    $sql = DB2Tools::extractDb2ObjectsFromLib(true, false, $ref_croisee);

    $sql_count = 'with tmp as ('.$sql.') select count(*) as comptage from tmp' ;

    if ($serveur1 == TYPE_ENVIR_APP) {
        $stmt_ref1 = DBWrapper::getStatement($cnx_db01, $sql, array($bib_a_analyser1));
        $tmp_comptage1 = DBWrapper::selectOne($cnx_db01, $sql_count, array($bib_a_analyser1)) ;
    } else {
        if ($serveur1 == TYPE_ENVIR_APP02) {
            $stmt_ref1 = DBWrapper::getStatement($cnx_db02, $sql, array($bib_a_analyser1));
            $tmp_comptage1 = DBWrapper::selectOne($cnx_db02, $sql_count, array($bib_a_analyser1)) ;
        } else {
            $stmt_ref1 = DBWrapper::getStatement($cnx_db03, $sql, array($bib_a_analyser1));
            $tmp_comptage1 = DBWrapper::selectOne($cnx_db03, $sql_count, array($bib_a_analyser1)) ;
        }
    }

    if ($serveur2 == TYPE_ENVIR_APP) {
        $stmt_ref2 = DBWrapper::getStatement($cnx_db01, $sql, array($bib_a_analyser2));
        $tmp_comptage2 = DBWrapper::selectOne($cnx_db01, $sql_count, array($bib_a_analyser1)) ;        
    } else {
        if ($serveur2 == TYPE_ENVIR_APP02) {
            $stmt_ref2 = DBWrapper::getStatement($cnx_db02, $sql, array($bib_a_analyser2));
            $tmp_comptage2 = DBWrapper::selectOne($cnx_db02, $sql_count, array($bib_a_analyser1)) ;               
        } else {
            $stmt_ref2 = DBWrapper::getStatement($cnx_db03, $sql, array($bib_a_analyser2));
            $tmp_comptage2 = DBWrapper::selectOne($cnx_db03, $sql_count, array($bib_a_analyser1)) ;               
        }
    }

    $comptage1 = isset($tmp_comptage1['COMPTAGE'])?$tmp_comptage1['COMPTAGE']:0 ;
    $comptage2 = isset($tmp_comptage2['COMPTAGE'])?$tmp_comptage2['COMPTAGE']:0 ;

    echo '<br/>Liste des écarts constatés entre les bibliothèques : ' . $serveur1 . '/' . $bib_a_analyser1 . ' et ' . $serveur2 . '/' . $bib_a_analyser2 . '<br/><br/>';

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

        $entete_tableau [1] = 'Nom table pour ' . $serveur1 . '/' . $bib_a_analyser1;
        $entete_tableau [7] = 'Nom table pour ' . $serveur2 . '/' . $bib_a_analyser2;

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

        echo 'Nbre d\'objets sur référentiel1 : ' . $comptage1 . '<br/>';
        echo 'Nbre d\'objets sur référentiel2 : ' . $comptage2 . '<br/>';
        echo 'Nbre d\'écarts comptabilisés    : ' . count($tab_ecarts) . '<br/>';

        echo '<table border="1" width="100%" cellspacing="0" cellpadding="5" >';
        echo '<tr class="header-row">';
        echo '<th>&nbsp;</th>';
        echo '<th scope="col">REF1 = ' . $serveur1 . '/' . $bib_a_analyser1 . '</th>';
        echo '<th scope="col">REF2 = ' . $serveur2 . '/' . $bib_a_analyser2 . '</th>';
        echo '<th>&nbsp;</th>';
        echo '</tr>';
        echo '<tr class="header-row">';
        $entete = '';
        foreach ($entete_tableau as $col) {
            $entete .= '<td>' . $col . '</td>';
        }
        echo $entete;
        echo '</tr>';
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
//affiche_sql_debug();
