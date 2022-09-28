<?php

if (array_key_exists('schema', $_GET) && array_key_exists('table', $_GET)) {
	$cnxdb = $this->getDB();
    $schema = Sanitize::blinderGet('schema');
    $table = Sanitize::blinderGet('table');
    $data = $cnxdb->selectOne(DB2Tools::extractTableInfo(), array($schema, $table));
    $datatyp = $data ['TABLE_TYPE'];

    if ($datatyp == 'V' || $datatyp == 'M') {

        echo '<div>' . PHP_EOL;
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

        $data = $cnxdb->selectBlock($sql, array($schema, $table));
        if (is_array($data) && count($data) > 0) {
            echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
            echo '<thead class="thead-dark">'.PHP_EOL;
            echo '<th>Object name</th><th>Object schema</th><th>Object type</th><th>Sys.tab.name</th><th>Sys.tab.schema</th><th>Table name</th></tr>' . PHP_EOL;
            echo '</thead>'.PHP_EOL;
            echo '<tbody>'.PHP_EOL;
            foreach ($data as $key => $value) {
                echo '<tr>' . PHP_EOL;
                if (trim($value ['OBJECT_TYPE']) == 'TABLE' || trim($value ['OBJECT_TYPE']) == 'VIEW') {
                    $get_params = 'schema=' . trim($value ['OBJECT_SCHEMA']) . '&amp;table=' . trim($value ['OBJECT_NAME']);
                    echo '<td><a href="dbTableDisplay?' . $get_params . '">' . trim($value ['OBJECT_NAME']) . '</a></td>' . PHP_EOL;
                } else {
                    if (trim($value ['OBJECT_TYPE']) == 'PROCEDURE' || trim($value ['OBJECT_TYPE']) == 'FUNCTION') {
                        $get_params = 'schema=' . trim($value ['OBJECT_SCHEMA']) . '&amp;routine=' . trim($value ['OBJECT_NAME']) .
                        '&amp;type=' . trim($value ['OBJECT_TYPE']);
                        echo '<td><a href="dbRoutineDisplay?' . $get_params . '">' . trim($value ['OBJECT_NAME']) . '</a></td>' . PHP_EOL;
                    } else {
                        echo '<td>' . trim($value ['OBJECT_NAME']) . '</td>' . PHP_EOL;
                    }
                }
                echo '<td>' . trim($value ['OBJECT_SCHEMA']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['OBJECT_TYPE']) . '</td>' . PHP_EOL;
                if ($value ['SYSTEM_TABLE_NAME']) {
                    echo '<td>' . trim($value ['SYSTEM_TABLE_NAME']) . '</td>' . PHP_EOL;
                } else {
                    echo '<td>&nbsp;</td>'.PHP_EOL;
                }
                if ($value ['SYSTEM_TABLE_SCHEMA']) {
                    echo '<td>' . trim($value ['SYSTEM_TABLE_SCHEMA']) . '</td>' . PHP_EOL;
                } else {
                    echo '<td>&nbsp;</td>'.PHP_EOL;
                }
                if ($value ['TABLE_NAME']) {
                    echo '<td>' . trim($value ['TABLE_NAME']) . '</td>' . PHP_EOL;
                } else {
                    echo '<td>&nbsp;</td>'.PHP_EOL;
                }
                echo '<tr>' . PHP_EOL;
            }
            echo '</tbody>'.PHP_EOL;
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
        echo '<br><br>'.PHP_EOL;
        echo '</fieldset>' . PHP_EOL;
        
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    } else {
        echo '<h3>Type d\'objet incorrect pour cette option </h3>';
    }
}
