<?php

if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$schema = Sanitize::blinderGet('schema') ;
	$table  = Sanitize::blinderGet('table') ;
    $cnxdb = $this->getDB();
	$sql = DB2Tools::extractTableInfo();
	$tablestats = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
        
	$system_schema = trim($tablestats['SYSTEM_TABLE_SCHEMA']) ;
	$system_table  = trim($tablestats['SYSTEM_TABLE_NAME']) ;

    $type_objet = $tablestats ['TABLE_TYPE'] ;
    echo '<fieldset>' . PHP_EOL ;
    $legende = 'D&eacute;finition de' ;
	if ($type_objet == 'V') {
		$cet_objet_est_une_vue = true;
		$legende .= ' la vue : '.$schema.'/'.$table ;
	} else {
		if ($type_objet == 'L') {
			$legende .= ' l\'index : '.$schema.'/'.$table ;
		} else {
            if ($type_objet == 'M') {
			    $legende .= ' la MQT : '.$schema.'/'.$table ;                        
            } else {
			    $legende .= ' la table : '.$schema.'/'.$table ;
            }
		}
	}
        
    echo '<legend><h6>'.$legende.'</h6></legend>'.PHP_EOL;
    
    echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
    echo '<thead class="thead-dark">'.PHP_EOL;
    echo '<tr class="header-row">';
    echo '<th>Statistique DB2</th><th>Valeur renvoyée par DB2</th>';
    echo '</tr>'.PHP_EOL ;
    echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
    foreach ( $tablestats as $key => $value ) {
        if (!is_null($value) && trim($value) != '') { 
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

