<?php
// TODO : brouillon à retravailler

if (array_key_exists('schema', $_GET) && array_key_exists('table', $_GET)) {

    $schema = strtoupper(Sanitize::blinderGet('schema'));
    $table = strtoupper(Sanitize::blinderGet('table'));
    $sql = DB2Tools::extractTableInfo();
    $data = $cnx_db01->selectOne($sql, array($schema, $table));

    $system_table_name = trim($data ['SYSTEM_TABLE_NAME']);
    $system_table_schema = trim($data ['SYSTEM_TABLE_SCHEMA']);
    $type_file = '*FILE';
    $member = '*ALL';
    $resultset = 'YES';

    $params = array();
    $params['OBJNAME'] = array('value' => $system_table_name, 'type' => 'in');
    $params['OBJLIB'] = array('value' => $system_table_schema, 'type' => 'in');
    $params['OBJTYPE'] = array('value' => $type_file, 'type' => 'in');
    $params['MEMBER'] = array('value' => $member, 'type' => 'in');
    $params['RESULTSET'] = array('value' => $resultset, 'type' => 'in');
    $result_set = true;

    $datas = $cnx_db01->callProcedure('APIOBJLCKP', BIB_REF_PGM, $params, $result_set);

    echo '<div class="container">' . PHP_EOL;
    echo '<br/><fieldset><legend>Liste des travaux verrouillant l\'objet ' . $schema . '/' . $table . '</legend>' . PHP_EOL;
    if ($system_table_name != $table) {
        echo 'Nom d\'objet système : ' . $system_table_schema . '/' . $system_table_name . '<br/>' . PHP_EOL;
    }

    if (count($datas) > 0) {
        echo '<table border="1" cellspacing="0" cellpadding="5" >' . PHP_EOL;
        $colnames = array(
            'JOB_NAME',
            'JOB_USER_NAME',
            'JOB_NUMBER',
            'LOCK_STATE',
            'LOCK_STATUS',
            'LOCK_TYPE',
            'MEMBER_NAME',
            'SHARE',
            'LOCK_SCOPE'
        );
        echo '<tr class="header-row">';
        echo '<td>';
        echo join('</td><td>', $colnames);
        foreach ($datas as $key => $value) {
            echo '<tr>';
            foreach ($colnames as $colname) {
                echo '<td>' . trim($value [$colname]) . '</td>' . PHP_EOL;
            }
            echo '<tr>' . PHP_EOL;
        }
        echo '</table>' . PHP_EOL;
    } else {
        echo 'Pas de verrouillage sur cette table DB2';
    }
    echo '</fieldset>' . PHP_EOL;
    echo '</div>' . PHP_EOL;
}


