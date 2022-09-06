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

    $sql = DB2Tools::extractSysroutinedepInverse();
    echo '<fieldset><legend><h6>Liste des routines DB2 utilisant la '.$type_objet.' ' .$routine.' </h6></legend>';

    $data = $cnxdb->selectBlock( $sql, array ( $specific['SPECIFIC_NAME'] ) );
    if (is_array($data) && count($data)>0) {
        echo '<div>'.PHP_EOL ;
        echo '<div class="container">'.PHP_EOL ;
        echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
        echo '<thead class="thead-dark">'.PHP_EOL;
        echo '<tr><th>Routine name</th><th>Routine schema</th><th>Routine type</th></tr>'.PHP_EOL;
        echo '</thead>'.PHP_EOL;
        echo '<tbody>'.PHP_EOL;		
        foreach($data as $key=>$value) {
            $get_params = 'schema='.trim($value ['ROUTINE_SCHEMA']).'&amp;routine='.trim($value ['ROUTINE_NAME']) .
                '&amp;type='.trim($value ['ROUTINE_TYPE']) ;

            echo '<tr>';
            //echo '<td>' . trim($value ['ROUTINE_NAME']) . '</td>';
            echo '<td><a href="dbRoutineDisplay?'.$get_params.'">' . trim($value ['ROUTINE_NAME']) . '</a></td>'.PHP_EOL;

            echo '<td>' . trim($value ['ROUTINE_SCHEMA']) . '</td>';
            echo '<td>' . trim($value ['ROUTINE_TYPE']) . '</td>';
            echo '<tr>' .PHP_EOL;
        }
        echo '</tbody>'.PHP_EOL;	
        echo '</table>'.PHP_EOL;
        echo '</fieldset>'.PHP_EOL;
        echo '</div>'.PHP_EOL ;
        echo '</div>'.PHP_EOL ;
    } else {	
        echo 'Néant'.PHP_EOL;		
        echo '<br/><br/>'.PHP_EOL;	
    }
    echo '</div>'.PHP_EOL;

    if ($type == 'FUNCTION') {
        // on regarde s'il y a des vues qui utilisent cette fonction 
        $sql = DB2Tools::extractSysviewdepInverse();
        echo '<fieldset><legend><h6>Liste des vues DB2 utilisant la fonction '. $routine.' </h6></legend>';

        $data = $cnxdb->selectBlock( $sql, array ( $schema, $routine ) );
        if (is_array($data) && count($data)>0) {
            echo '<div>'.PHP_EOL ;
            echo '<div class="container">'.PHP_EOL ;
            echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
            echo '<thead class="thead-dark">'.PHP_EOL;
            echo '<tr><th>View name</th><th>View schema</th><th>View owner</th></tr>'.PHP_EOL;
            echo '</thead>'.PHP_EOL;
            echo '<tbody>'.PHP_EOL;		
            foreach($data as $key=>$value) {
                $get_params = 'schema='.trim($value ['VIEW_SCHEMA']).'&amp;table='.trim($value ['VIEW_NAME']) ;
                echo '<tr>';
                echo '<td><a href="dbTableDisplay?'.$get_params.'">' . trim($value ['VIEW_NAME']) . '</a></td>'.PHP_EOL;

                echo '<td>' . trim($value ['VIEW_SCHEMA']) . '</td>';
                echo '<td>' . trim($value ['VIEW_OWNER']) . '</td>';
                echo '<tr>' .PHP_EOL;
            }
            echo '</tbody>'.PHP_EOL;	
            echo '</table>'.PHP_EOL;
            echo '</fieldset>'.PHP_EOL;
            echo '</div>'.PHP_EOL ;
            echo '</div>'.PHP_EOL ;
        } else {	
            echo 'Néant'.PHP_EOL;		
            echo '<br/><br/>'.PHP_EOL;	
        }
        echo '</div>'.PHP_EOL;
    }
}