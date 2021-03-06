<?php
$currentScript = 'dbColumnSearch';
 
// récupération des paramètres de $_GET
$fields = array(
    'nom_col',
    'nom_base',
    'nom_table'
);

$params = array();
foreach ($fields as $field) {
    $params [$field] = Sanitize::blinderGet($field);
}

// récupération ou initialisation de l'offset
$offset = isset($_GET ['offset']) ? Sanitize::blinderGet('offset', '', 'intval') : 1;
$params ['offset'] = $offset;
?>
<fieldset>
    <legend>Analyse des colonnes de tables DB2</legend>
    <form id="extraction" name="extraction" method="get" action="" >
    <div class="form-group row">
        <label for="nom_col" class="col-sm-5 col-form-label">Saisissez un nom colonne SQL (obligatoire) (*) :</label>
        <div class="col-sm-3">
            <input type="text" name="nom_col" id="nom_col" class="form-control" size="20"
                value="<?php echo array_key_exists('nom_col', $params) ? $params ['nom_col'] : ''; ?>" /> 
        </div>
        <div class="col-sm-3">
        <!--    <img src="images/search.gif" id="nom_col_icon" onclick="$('#nom_col').focus();" alt="search" />  -->
            <img src="images/clear_left.png" id="nom_col_clear" onclick="$('#nom_col').val('');" alt="clear"/>
        </div>
    </div>
    <div class="form-group row">
        <label for="nom_base" class="col-sm-5 col-form-label">Saisissez une bibliothèque (nom long, facultatif) (**):</label>
        <div class="col-sm-3">
            <input type="text" name="nom_base" id="nom_base" class="form-control" size="20" 
                value="<?php echo array_key_exists('nom_base', $params) ? $params ['nom_base'] : ''; ?>" /> 
        </div>
        <div class="col-sm-3">
        <!--    <img src="images/search.gif" id="nom_base_icon" onclick="$('#nom_base').focus();" alt="search" />  -->
            <img src="images/clear_left.png" id="nom_base_clear" onclick="$('#nom_base').val('');" alt="search" />
        </div>
    </div>
    <div class="form-group row">
        <label for="nom_table" class="col-sm-5 col-form-label">Saisissez une table SQL (nom long, facultatif) (**):</label>
        <div class="col-sm-3">
            <input type="text" name="nom_table" id="nom_table" class="form-control" 
                value="<?php echo array_key_exists('nom_table', $params) ? $params ['nom_table'] : ''; ?>"  /> 
        </div>
        <div class="col-sm-3">
            <img src="images/clear_left.png" id="nom_table_clear" onclick="$('#nom_table').val('');" alt="clear" />
        </div>
    </div>
        <p><h6><small>(*) : Le nom de colonne peut être un nom court ou un nom long. On peut utiliser les 
            jokers SQL _ et % pour déclencher des recherches de type "contient".
</small></h6></p>
        <p><h6><small>(**) : Pour les noms de bibliothèque et de table, il est possible d'indiquer des noms partiels. 
            Ne saisissez jamais de % dans ces 2 zones, car la recherche sera dans tous les cas de type "contient".</small></h6></p>
        <input type="submit" value="valider" name="crud_valid" id="crud_valid" class="btn btn-primary" />
<!--        <input type="submit" value="export_csv" name="crud_export_csv"
               id="crud_export_csv" /> -->
    </form>
</fieldset>
<?php
if (array_key_exists('nom_col', $params) && $params['nom_col'] != '' && array_key_exists('nom_base', $params) && array_key_exists('nom_table', $params)) {
    $cnxdb = $this->getDB();
    $criteres = array();
    $jokers = array();
    foreach ($params as $key => $value) {
        if ($key != 'offset') {
            $temp = trim(strtoupper($value));
            if ($temp != '') {
                if ($key == 'nom_col') {
                    $criteres [] = $temp;
                    $criteres [] = $temp; // Critère doublé pour recherche sur nom long et court
                } else {
                    // critères avec LIKE
                    $criteres [] = $temp . '%';
                }
            }
        }
    }

    $sql = DB2Tools::getTablesByColumn($params ['nom_col'], 
        $params ['nom_base'], $params ['nom_table']);

    $nb_lignes_total = $cnxdb->countNbRowsFromSQL($sql, $criteres);

    if (is_null($nb_lignes_total) || $nb_lignes_total <= 0) {
        echo 'pas de données trouvées';
    } else {

        if (array_key_exists('crud_export_csv', $_GET)) {
            ob_clean();
            ob_start();

            ExportOffice::csv('extract_tables_db2');

            echo $cnxdb->export2CSV($sql, $criteres);

            ob_end_flush();
            exit();
        } else {
            //$cnxdb->setProfilerOn() ;
            // $datas = $cnxdb->getScrollCursor($sql, $criteres, $offset, MAX_LINES_BY_PAGE, 'TABLE_NAME');
            $datas = $cnxdb->getPagination ( $sql, $criteres, $offset, MAX_LINES_BY_PAGE, 'TABLE_NAME' );

            $lastRowNumber = 0;
            echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
            echo '<thead class="thead-dark">'.PHP_EOL;
            echo '<tr><th>Nom col. long</th><th>Nom col. court</th><th>Schéma</th><th>Table</th><th>Libell&eacute;</th><th>Type</th><th>Longueur</th><th>Précision</th><th>Null</th><th>Identité</th></tr>' . PHP_EOL;
            echo '</thead>'.PHP_EOL;
            echo '<tbody>'.PHP_EOL;
            foreach ($datas as $data) {
                echo '<tr>' . PHP_EOL;
                echo '<td>' . trim($data ['COLUMN_NAME']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($data ['SYSTEM_COLUMN_NAME']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($data ['TABLE_SCHEMA']) . '</td>' . PHP_EOL;
                echo '<td>' . HtmlToolbox::genHtmlLink('dbTableDisplay?schema=' . trim($data ['TABLE_SCHEMA']) . '&table=' . trim($data ['TABLE_NAME']), trim($data ['TABLE_NAME'])) . '</td>' . PHP_EOL;
                if (trim($data ['COLUMN_TEXT']) == '') {
                    echo '<td>' . trim($data ['COLUMN_HEADING']) . '</td>' . PHP_EOL;
                } else {
                    echo '<td>' . trim($data ['COLUMN_TEXT']) . '</td>' . PHP_EOL;
                }
                if (trim($data ['DATA_TYPE']) == 'VARCHAR') {
                    echo '<td><font color = "red">' . $data ['DATA_TYPE'] . '</font></td>' . PHP_EOL;
                } else {
                    echo '<td>' . $data ['DATA_TYPE'] . '</td>' . PHP_EOL;
                }
                if (!is_null($data ['NUMERIC_PRECISION'])) {
                    $longueur = $data ['NUMERIC_PRECISION'];
                } else {
                    $longueur = $data ['LENGTH'];
                }
                echo '<td align="right">' . $longueur . '</td>' . PHP_EOL;
                echo '<td align="right">' . $data ['SCALE'] . '</td>' . PHP_EOL;
                echo '<td align="center">' . $data ['COLUMN_NULLABLE'] . '</td>' . PHP_EOL;
                echo '<td align="center">' . $data ['IS_IDENTITY'] . '</td>' . PHP_EOL;
                echo '</tr>' . PHP_EOL;
                $lastRowNumber++;
            }
            echo '</tbody>'.PHP_EOL;
            echo '</table>' . PHP_EOL;
            echo '<br/>';
            // Appel de la fonction de pagination
            echo Pagination::pcIndexedLinks ( $nb_lignes_total, $offset, MAX_LINES_BY_PAGE, $currentScript, $params );

            echo '<br/>' . PHP_EOL;
            echo "(Affichage " . $offset . " à " . ($offset + $lastRowNumber - 1) . " sur " . $nb_lignes_total . ")";
        }
    }
}

