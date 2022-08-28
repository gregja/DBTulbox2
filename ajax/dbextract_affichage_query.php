<?php
// TODO : brouillon à retravailler
echo <<<CODE_JS
<script>
/**
  * Inserts multiple fields.
  * code emprunté au logiciel PHPMyAdmin
  */
function insertValueQuery() {
    var myQuery = document.sqlform.sql_query;
    var myListBox = document.sqlform.dummy;

    if(myListBox.options.length > 0) {
        sql_box_locked = true;
        var chaineAj = "";
        var NbSelect = 0;
        for(var i=0; i<myListBox.options.length; i++) {
            if (myListBox.options[i].selected){
                NbSelect++;
                if (NbSelect > 1)
                    chaineAj += ", ";
                chaineAj += myListBox.options[i].value;
            }
        }

        //IE support
        if (document.selection) {
            myQuery.focus();
            sel = document.selection.createRange();
            sel.text = chaineAj;
            document.sqlform.insert.focus();
        }
        //MOZILLA/NETSCAPE support
        else if (document.sqlform.sql_query.selectionStart || document.sqlform.sql_query.selectionStart == "0") {
            var startPos = document.sqlform.sql_query.selectionStart;
            var endPos = document.sqlform.sql_query.selectionEnd;
            var chaineSql = document.sqlform.sql_query.value;

            myQuery.value = chaineSql.substring(0, startPos) + chaineAj + chaineSql.substring(endPos, chaineSql.length);
        } else {
            myQuery.value += chaineAj;
        }
        sql_box_locked = false;
    }
}
</script>
CODE_JS;
/*
$params_page = array(
    'js_specif_code' => $js_code,
    'css_specif_file' => 'querybox.css'
        );
*/
$cnxdb = $this->getDB();
$limit_sql = 1000;

if (isset($_POST) && count($_POST) > 0) {
    $schema = Sanitize::blinderPost('schema_name');
    $table = Sanitize::blinderPost('table_name');
    $sql_query = Sanitize::blinderPost('sql_query');
    $offset = 1;
} else {
    $schema = Sanitize::blinderGet('schema');
    $table = Sanitize::blinderGet('table');
    if (isset($_GET ['offset'])) {
        $offset = Sanitize::blinderGet('offset', '', 'intval');
        if ($offset <= 0) {
            $offset = 1;
        }
        $sql_query = isset($_SESSION['requetage']['sql_query']) ? $_SESSION['requetage']['sql_query'] : '';
    } else {
        $offset = 1;
        $sql_query = '';
    }
}

// paramètres conservés pour transmission à la fonction de pagination
$params = array();
$params ['schema'] = $schema;
$params ['table'] = $table;
$params ['offset'] = $offset;

$sql = DB2Tools::extractTableStruct(false, true);
$liste_cols = $cnxdb->selectBlock($sql, array($schema, $table));

if ($sql_query != '') {
    $sql_base = $sql_query;
} else {
    $sql_base = 'SELECT A.* ' . PHP_EOL . ' FROM {TABLE} A';
}
echo PHP_EOL ;
?>
<form id="sqlform" name="sqlform" method="post" action="">
    <fieldset><legend>Outil de Query simplifié sur la table : <?php echo $schema . '/' . $table; ?></legend>
        <p>Avertissement : ne pas remplacer le tag {TABLE}, c'est le requêteur qui s'en charge.</p>
        <p>Pour des raisons de sécurité, les opérateurs de comparaison SQL doivent être remplacés par : *EQ, *NE, *LT, *LE, *GT, *GE</p>
        <input id="table_name" name="table_name" type="hidden" value="<?php echo $table; ?>" />
        <input id="schema_name" name="schema_name" type="hidden" value="<?php echo $schema; ?>" />
        <div id="queryfieldscontainer">
            <div id="sqlquerycontainer">
                <textarea name="sql_query" id="sqlquery" cols="40" rows="15" dir="ltr" onkeypress="document.sqlform.elements['LockFromUpdate'].checked = true;">
                    <?php echo $sql_base; ?>
                </textarea>
            </div>
            <div id="tablefieldscontainer">
                <label>Champs</label>
                <select id="tablefields" name="dummy" size="13" multiple="multiple" ondblclick="insertValueQuery();">
                    <?php
                    foreach ($liste_cols as $key => $value) {
                        echo '<option value="A.' . $value['FIELD'] . '" title="">A.' . $value['FIELD'] . '</option>' . PHP_EOL;
                    }
                    ?>
                </select>
                <div id="tablefieldinsertbuttoncontainer">

                    <input name="insert" value="&lt;&lt;" onclick="insertValueQuery()" title="Insérer" type="button" />
                </div>
            </div>
            <div class="clearfloat"></div>
        </div>
        <div class="clearfloat"></div>
    </fieldset>
    <fieldset id="queryboxfooter" class="tblFooters">
        <input type="submit" name="SQL" value="Exécuter" />
        <input type="submit" value="export_csv" name="crud_export_csv" id="crud_export_csv" />
        <input type="submit" value="export_xml" name="crud_export_xml" id="crud_export_xml" />
        <input type="submit" value="export_sql" name="crud_export_sql" id="crud_export_sql" />
        Attention, les fonctions d'export sont pour l'instant limitées aux <?php echo $limit_sql; ?> premières lignes.
        <div class="clearfloat"></div>
    </fieldset>
</form>
<?php
$sql = '';
if (isset($_POST) && $sql_query != '') {
    // requête avant modif conservée pour stockage en session
    $tmp_sql_query = $sql_query;

    //$pos = strpos ( $sql_query, '{TABLE}' );
    //if ($pos === false) {
    //echo 'Le tag {TABLE} a été supprimé, la requête ne peut être traitée<br/>';
    //} else {
    $sql_query = str_replace('{TABLE}', trim($schema) . '{SEPARATOR}' . trim($table), $sql_query);
    $sql_query = Sanitize::replace_sql_operators($sql_query);

    $nb_lignes_total = $cnxdb->countNbRowsFromSQL($sql_query, array());

    if (is_null($nb_lignes_total)) {
        echo 'ERREUR : requête inopérante<br/>';
    } else {
        if ($nb_lignes_total <= 0) {
            echo 'La requête fonctionne mais ne renvoie aucune donnée<br/>';
        } else {
            // stockage de la requête SQL courante en session pour réutilisation après pagination
            // (elle est transmise en session par précaution car elle risque d'être trop longue pour
            // pouvoir être transmise en $_GET)
            $_SESSION['requetage']['sql_query'] = $tmp_sql_query;

            if (array_key_exists('crud_export_csv', $_POST)
                    || array_key_exists('crud_export_xml', $_POST)
                    || array_key_exists('crud_export_sql', $_POST)) {
                $sql_query .= ' FETCH FIRST ' . $limit_sql . ' ROWS ONLY';

                ob_clean();
                ob_start();

                if (array_key_exists('crud_export_csv', $_POST)) {
                    ExportOffice::csv('extract_tables_db2');
                    echo $cnxdb->export2CSV($sql_query);
                } else {
                    if (array_key_exists('crud_export_xml', $_POST)) {
                        ExportOffice::txt('extract_tables_db2');
                        echo $cnxdb->export2XML($sql_query);
                    } else {
                        ExportOffice::txt('extract_tables_db2');
                        echo $cnxdb->export2insertSQL($sql_query);
                    }
                }

                ob_end_flush();
                exit();
            } else {
                $cnxdb->setProfilerOn();
                //$datas = $cnxdb->getPagination ( $sql_query, array(), $offset, MAX_LINES_BY_PAGE );
                $datas = $cnxdb->getScrollCursor($sql_query, array(), $offset, MAX_LINES_BY_PAGE);
                //$cnxdb->setProfilerOff() ;
                if (is_array($datas) && count($datas) > 0) {
                    echo '<table border="1" width="100%" cellspacing="0" cellpadding="5" >';
                    echo '<tr class="header-row">';
                    foreach ($datas[0] as $key => $value) {
                        echo '<td>' . trim($key) . '</td>';
                    }
                    echo '</tr>' . PHP_EOL;

                    $lastRowNumber = 0;
                    foreach ($datas as $data) {
                        echo '<tr>';
                        foreach ($data as $key => $value) {
                            if (is_int($value) || is_float($value)) {
                                // cet alignement ne sera pas pris en compte avec PDO qui renvoie des données de type string
                                // par contre il fonctionnera avec DB2_Connect qui renvoie des données correctement typées
                                $align = 'right';
                            } else {
                                $align = 'left';
                                $value = trim($value);
                            }
                            echo '<td align="' . $align . '">' . htmlentities($value) . '</td>';
                        }
                        echo '</tr>';
                        $lastRowNumber++;
                    }
                    echo '</table>';
                }
                echo '<br/>';
                // Appel de la fonction de pagination
                DBPagination::pcIndexedLinks($nb_lignes_total, $offset, MAX_LINES_BY_PAGE, $_SERVER ['PHP_SELF'], $params);
                echo '<br/>';
                echo "(Affichage " . $offset . " à " . ($offset + $lastRowNumber - 1) . " sur " . $nb_lignes_total . ")";

                echo '<fieldset><legend>Requête SQL générée :</legend>';
                echo SQLTools::coloriseCode($sql_query);
                echo '</fieldset><br/>';
            }
        }
    }
    //}
}

//affiche_sql_debug($sql_query);


