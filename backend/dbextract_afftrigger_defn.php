<?php

if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'trigger', $_GET )) {
	$schema = Sanitize::blinderGet('schema') ;
	$table  = Sanitize::blinderGet('trigger') ;
    $cnxdb = $this->getDB();

    $trigdefn = $cnxdb->selectOne( DB2Tools::getTriggerDesc(), array ( $schema, $trigger ) );

    echo '<fieldset>' . PHP_EOL ;
    $legende = 'D&eacute;finition du trigger ' . $schema.'/'.$trigger ;
        
    echo '<legend><h6>'.$legende.'</h6></legend>'.PHP_EOL;
    
    echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
    echo '<thead class="thead-dark">'.PHP_EOL;
    echo '<tr class="header-row">';
    echo '<th>Colonne</th><th>Valeur renvoyée par DB2</th>';
    echo '</tr>'.PHP_EOL ;
    echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
    foreach ( $trigdefn as $key => $value ) {
        if ($key != 'ACTION_STATEMENT' && !is_null($value) && trim($value) != '') { 
            echo '<tr>'.PHP_EOL ;
            echo '<td>&nbsp;' . trim($key) . '&nbsp;</td>'.PHP_EOL ;
            echo '<td>&nbsp;' . trim($value) . '&nbsp;</td>'.PHP_EOL ;
            echo '<tr>'. PHP_EOL ;
        }
    }
    echo '</tbody>'.PHP_EOL;
    echo '</table>'.PHP_EOL ;
    
    echo '</fieldset>' . PHP_EOL ;
        
	echo '<div>'.PHP_EOL ;

	echo '</div>'.PHP_EOL ;		
}

