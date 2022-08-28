<?php

if (array_key_exists('schema', $_GET) && array_key_exists('table', $_GET)) {
	$cnxdb = $this->getDB();
    $schema = Sanitize::blinderGet('schema');
    $table = Sanitize::blinderGet('table');
    $data = $cnxdb->selectOne(DB2Tools::extractTableInfo(), array($schema, $table));
    $datatyp = $data ['TABLE_TYPE'];

    if ($datatyp == 'T' || $datatyp == 'P' || $datatyp == 'M') {

        echo '<div>' . PHP_EOL;
        echo '<div class="container">' . PHP_EOL;

        $sql = DB2Tools::getTableTriggers();

        echo '<fieldset><legend>Liste des triggers liés à ' .
            $schema . '/' . $table . '</legend>' . PHP_EOL;

        $data = $cnxdb->selectBlock($sql, array($schema, $table));
        if (is_array($data) && count($data) > 0) {
            echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
            echo '<thead class="thead-dark">'.PHP_EOL;
            echo '<th>Trigger name</th><th>Trigger schema</th><th>Event type</th><th>Timing</th><th>Ord.</th><th>Trig.Mode</th><th>Création</th><th>Nom court</th></tr>' . PHP_EOL;
            echo '</thead>'.PHP_EOL;
            echo '<tbody>'.PHP_EOL;
            foreach ($data as $key => $value) {
                $get_params = 'schema=' . trim($value ['TRIGGER_SCHEMA']) . '&amp;trigger=' . trim($value ['TRIGGER_NAME']);
                echo '<tr>' . PHP_EOL;
                echo '<td><a href="dbTriggerDisplay?' . $get_params . '">' . trim($value ['TRIGGER_NAME']) . '</a></td>' . PHP_EOL;
                echo '<td>' . trim($value ['TRIGGER_SCHEMA']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['EVENT_MANIPULATION']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['ACTION_TIMING']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['ACTION_ORDER']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['TRIGGER_MODE']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['CREADATE']) . '</td>' . PHP_EOL;
                echo '<td>' . trim($value ['TRIGGER_PROGRAM_NAME']) . '</td>' . PHP_EOL;
                //echo '<td>&nbsp;</td>'.PHP_EOL;
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
        
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    } else {
        echo '<h3>Type d\'objet incorrect pour cette option </h3>';
    }
}
