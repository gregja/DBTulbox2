<?php
if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'routine', $_GET ) && array_key_exists('type', $_GET)) {
	$cnxdb = $this->getDB();	
	$schema = Sanitize::blinderGet ( 'schema');
	$routine = Sanitize::blinderGet ( 'routine');
	$type = Sanitize::blinderGet ( 'type');

    if ($type == 'PROCEDURE') {
        $type_objet = 'procédure stockée';
    } else {
        $type_objet = 'fonction';
    }

    $specific = $cnxdb->selectOne( DB2Tools::getRoutineSpecificName(), array ($schema, $routine ) );    

    echo '<fieldset><legend><h6>Liste des objets DB2 utilisés par la '.$type_objet.' ' .$routine.' </h6></legend>';
    $data = $cnxdb->selectBlock( DB2Tools::extractSysroutinedep(),
        array ($specific['SPECIFIC_SCHEMA'], $specific['SPECIFIC_NAME'] ) );
    if (is_array($data) && count($data)>0) {
        echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
        echo '<thead class="thead-dark">'.PHP_EOL;
        echo '<tr><th>Object name</th><th>Object schema</th><th>Object type</th></tr>'.PHP_EOL;	
        echo '</thead>'.PHP_EOL;
        echo '<tbody>'.PHP_EOL;		
        foreach($data as $key=>$value) {
            $get_params = 'schema=' . trim($value ['OBJECT_SCHEMA']) . '&amp;table=' . trim($value ['OBJECT_NAME']);
            echo '<tr>' . PHP_EOL;
            if (trim($value ['OBJECT_TYPE']) == 'TABLE' || trim($value ['OBJECT_TYPE']) == 'VIEW') {
                echo '<td><a href="dbTableDisplay?' . $get_params . '">' . trim($value ['OBJECT_NAME']) . '</a></td>' . PHP_EOL;
            } else {
                echo '<td>' . trim($value ['OBJECT_NAME']) . '</td>' . PHP_EOL;
            }
            echo '<td>' . trim($value ['OBJECT_SCHEMA']) . '</td>';
            echo '<td>' . trim($value ['OBJECT_TYPE']) . '</td>';
            echo '<tr>' ;
        }
        echo '</tbody>'.PHP_EOL;		
        echo '</table>'.PHP_EOL;		
    } else {
        echo '<br/>'.PHP_EOL;		
        echo 'Néant'.PHP_EOL;		
        echo '<br/>'.PHP_EOL;		
    }
    echo '</fieldset>'.PHP_EOL;		
    echo '</div>'.PHP_EOL ;     

}