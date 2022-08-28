<?php

if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$cnxdb = $this->getDB();
    $schema = strtoupper(Sanitize::blinderGet('schema'));
    $table = strtoupper(Sanitize::blinderGet('table'));

    $data = $cnxdb->selectOne(DB2Tools::extractTableInfo(), array($schema, $table));

    $system_table_name = trim($data ['SYSTEM_TABLE_NAME']);
    $system_table_schema = trim($data ['SYSTEM_TABLE_SCHEMA']);

    $datas = $cnxdb->selectBlock(DB2Tools::getTableLocks(), array($system_table_name, $system_table_schema));
    echo '<div class="container">' . PHP_EOL;

    echo '<br/><fieldset><legend>Liste des travaux verrouillant l\'objet ' . $schema . '/' . $table . '</legend>' . PHP_EOL;
    if ($system_table_name != $table) {
        echo 'Nom d\'objet système : ' . $system_table_schema . '/' . $system_table_name . '<br><br>' . PHP_EOL;
    }
    echo '<br>'.PHP_EOL;

    if (count($datas) > 0) {
        echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
        echo '<thead class="thead-dark">'.PHP_EOL;
        $colnames = array(
            'SYSTEM_TABLE_MEMBER',
            'RELATIVE_RECORD_NUMBER',
            'JOB_NAME',
            'LOCK_STATE',
            'LOCK_STATUS',
            'LOCK_SCOPE'
        );
        echo '<tr>'.PHP_EOL.'<th>'.PHP_EOL;
        echo join('</th><th>', $colnames);
        echo PHP_EOL.'</th>'.PHP_EOL;
        echo '</tr>'.PHP_EOL;
        echo '</thead>'.PHP_EOL;
        echo '<tbody>'.PHP_EOL;
        foreach ($datas as $key => $value) {
            echo '<tr>';
            foreach ($colnames as $colname) {
                echo '<td>' . trim($value [$colname]) . '</td>' . PHP_EOL;
            }
            echo '<tr>' . PHP_EOL;
        }
        echo '</tbody>'.PHP_EOL;
        echo '</table>' . PHP_EOL;
    } else {
        echo '<h4>Pas de verrouillage sur cette table DB2</h4>';
    }
    echo '</fieldset>' . PHP_EOL;
    echo '</div>' . PHP_EOL;
}


