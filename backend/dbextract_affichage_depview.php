<?php
// TODO : brouillon à retravailler

if (array_key_exists('schema', $_GET) && array_key_exists('table', $_GET)) {

    $schema = Sanitize::blinderGet('schema');
    $table = Sanitize::blinderGet('table');
    $sql = DB2Tools::extractTableInfo();
    $data = $cnx_db01->selectOne($sql, array($schema, $table));
    $datatyp = $data ['TABLE_TYPE'];

    if ($datatyp == 'V' || $datatyp == 'M') {

        echo '<div>' . PHP_EOL;
        //echo '<h4 href="#">>Liste des objets utilisés</h4>'.PHP_EOL;
        echo '<div class="container">' . PHP_EOL;

        if ($datatyp == 'V') {
            $sql = DB2Tools::extractSysviewdep();
            $type_obj = 'vue';
        } else {
            $sql = DB2Tools::extractSystabledep();
            $type_obj = 'MQT';
        }
        echo '<fieldset><legend>Liste des objets utilisés par la ' .
        $type_obj . ' ' . $schema . '/' . $table . '</legend>' . PHP_EOL;

        $data = $cnx_db01->selectBlock($sql, array($schema, $table));
        if (is_array($data) && count($data) > 0) {
            echo '<table border="1" cellspacing="0" cellpadding="5" >' . PHP_EOL;
            echo '<tr class="header-row"><td>Object name</td><td>Object schema</td><td>Object type</td><td>Sys.tab.name</td><td>Sys.tab.schema</td><td>Table name</td></tr>' . PHP_EOL;
            foreach ($data as $key => $value) {
                $get_params = 'schema=' . trim($value ['OBJECT_SCHEMA']) . '&amp;table=' . trim($value ['OBJECT_NAME']);
                echo '<tr>' . PHP_EOL;
                if (trim($value ['OBJECT_TYPE']) == 'TABLE' || trim($value ['OBJECT_TYPE']) == 'VIEW') {
                    echo '<td><a href="dbextract_affichage.php?' . $get_params . '">' . trim($value ['OBJECT_NAME']) . '</a></td>' . PHP_EOL;
                } else {
                    echo '<td>' . trim($value ['OBJECT_NAME']) . '</td>' . PHP_EOL;
                }
                echo '<td>' . trim($value ['OBJECT_SCHEMA']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['OBJECT_TYPE']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['SYSTEM_TABLE_NAME']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['SYSTEM_TABLE_SCHEMA']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['TABLE_NAME']) . '</td>' . PHP_EOL;
                echo '<tr>' . PHP_EOL;
            }
            echo '</table>' . PHP_EOL;
        } else {
            echo '<br/>';
            echo 'Néant';
            echo '<br/>' . PHP_EOL;
        }
        echo '</fieldset>' . PHP_EOL;

        echo '<br>'.PHP_EOL ;
        
        echo '<fieldset>' . PHP_EOL;        
        $get_params = "schema=$schema&amp;table=$table" ;
        echo '<br>'.PHP_EOL;
        echo '<a href="dbextract_affichage_depview2.php?'.$get_params.'" target="_blank">Arbre des dépendances</a>'.PHP_EOL ;
        echo '<br><br>'.PHP_EOL;
        echo '</fieldset>' . PHP_EOL;
        
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }
}
