<?php

if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$cnxdb = $this->getDB();
	$schema = Sanitize::blinderGet ( 'schema');
	$table = Sanitize::blinderGet ( 'table');

	$data = $cnxdb->selectOne ( DB2Tools::extractTableInfo (), array ($schema, $table ) );
	if ($data ['TABLE_TYPE'] == 'V') {
		$cet_objet_est_une_vue = true;
	} else {
		$cet_objet_est_une_vue = false;
	}
	$system_table_schema = $data['SYSTEM_TABLE_SCHEMA'];
	$system_table_name = $data['SYSTEM_TABLE_NAME'];

	// recherche des dépendances en se basant sur le nom court de l'objet DB2 principal
	// l'objectif étant de récupérer la liste des indexs de type DDS, qui sont susceptibles 
	// d'être utilisés par d'autres objets DB2 (notamment des indexs de type "surrogate")
	list($cmd, $sql) = DB2Tools::extractDspDbr($system_table_name, $system_table_schema);
	$cnxdb->executeSysCommand($cmd);
	$dataindexs = $cnxdb->selectBlock($sql);

	/**
	 * Affichage des vues utilisant un objet DB2
	 */
	function sysViewDepInverse($cnxdb, $system_table_schema, $system_table_name) {

		$data = $cnxdb->selectBlock( DB2Tools::extractSysviewdepInverse(), 
			array ($system_table_schema, $system_table_name ) );
		if (is_array($data) && count($data)>0) {
			echo '<div>'.PHP_EOL ;
			echo '<div class="container">'.PHP_EOL ;
		
			echo '<br/><fieldset><legend>Liste des Vues utilisant l\'objet '.$system_table_schema.'/'.$system_table_name.'</legend>'.PHP_EOL;
			echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
			echo '<thead class="thead-dark">'.PHP_EOL;
			echo '<tr><th>View name</th><th>View schema</th><th>View owner</th></tr>' . PHP_EOL;
			echo '</thead>'.PHP_EOL;
			echo '<tbody>'.PHP_EOL;

			foreach($data as $key=>$value) {
				$get_params = 'schema='.trim($value ['VIEW_SCHEMA']).'&amp;table='.trim($value ['VIEW_NAME']);
				echo '<tr>';
				echo '<td><a href="dbTableDisplay?'.$get_params.'">' . trim($value ['VIEW_NAME']) . '</a></td>'.PHP_EOL;
				echo '<td>' . trim($value ['VIEW_SCHEMA']) . '</td>'.PHP_EOL;
				echo '<td>' . trim($value ['VIEW_OWNER']) . '</td>'.PHP_EOL;
				echo '<tr>' .PHP_EOL;
			}
			echo '</tbody>'.PHP_EOL;
			echo '</table>'.PHP_EOL;
			echo '</fieldset>'.PHP_EOL;
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
		}
	}
	
	/**
	 * Affichage des routines (procédures stockées et UDF/UDTF) utilisant un objet DB2
	 */
	function sysRoutineDepInverse($cnxdb, $system_table_schema, $system_table_name) {
		
		$data = $cnxdb->selectBlock( DB2Tools::extractSysroutinedepInverse(), array ( $system_table_name ) );
		if (is_array($data) && count($data)>0) {
			echo '<div>'.PHP_EOL ;
			echo '<div class="container">'.PHP_EOL ;
			
			echo '<br/><fieldset><legend>Liste des routines utilisant l\'objet '. $system_table_schema . '.' .
				$system_table_name.'</legend>'.PHP_EOL;
			echo 'Attention : cette liste est théorique, car la recherche est effectuée sur le nom de l\'objet considéré, sans la bibliothèque,<br/>';
			echo 'cette dernière n\'étant pas toujours "qualifiée" dans les routines (procédures et fonctions) référencées (Object schema = *LIBL).<br/><br/>'.PHP_EOL ; 
			echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
			echo '<thead class="thead-dark">'.PHP_EOL;
			echo '<tr><th>Routine name</th><th>Routine schema</th><th>Routine type</th></tr>' . PHP_EOL;
			echo '</thead>'.PHP_EOL;
			echo '<tbody>'.PHP_EOL;
			foreach($data as $value) {
				$get_params = 'schema='.trim($value ['ROUTINE_SCHEMA']).'&amp;routine='.trim($value ['ROUTINE_NAME']) .
					'&amp;type='.trim($value ['ROUTINE_TYPE']) ;
				echo '<tr>'.PHP_EOL;
				echo '<td><a href="dbRoutineDisplay?'.$get_params.'">' . trim($value ['ROUTINE_NAME']) . '</a></td>'.PHP_EOL;
				echo '<td>' . trim($value ['ROUTINE_SCHEMA']) . '</td>'.PHP_EOL;
				echo '<td>' . trim($value ['ROUTINE_TYPE']) . '</td>'.PHP_EOL;
				echo '<tr>' .PHP_EOL;
			}
			echo '</tbody>'.PHP_EOL;
			echo '</table>'.PHP_EOL;
			echo '</fieldset>'.PHP_EOL;
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
		}
	}

	/**
	 * Affichage des triggers utilisant un objet DB2
	 */
	function sysTriggerDepInverse($cnxdb, $system_table_schema, $system_table_name) {

		$data = $cnxdb->selectBlock( DB2Tools::extractSystrigdepInverse(), 
			array ($system_table_schema, $system_table_name ) );
		if (is_array($data) && count($data)>0) {
			echo '<div>'.PHP_EOL ;
			echo '<div class="container">'.PHP_EOL ;
		
			echo '<br/><fieldset><legend>Liste des Triggers utilisant l\'objet '.$system_table_schema.'/'.$system_table_name.'</legend>'.PHP_EOL;
			echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
			echo '<thead class="thead-dark">'.PHP_EOL;
			echo '<tr><th>Trigger name</th><th>Trigger schema</th></tr>' . PHP_EOL;
			echo '</thead>'.PHP_EOL;
			echo '<tbody>'.PHP_EOL;

			foreach($data as $key=>$value) {
				$get_params = 'schema='.trim($value ['TRIGGER_SCHEMA']).'&amp;trigger='.trim($value ['TRIGGER_NAME']);
				echo '<tr>';
				echo '<td><a href="dbTriggerDisplay?'.$get_params.'">' . trim($value ['TRIGGER_NAME']) . '</a></td>'.PHP_EOL;
				echo '<td>' . trim($value ['TRIGGER_SCHEMA']) . '</td>'.PHP_EOL;
				echo '<tr>' .PHP_EOL;
			}
			echo '</tbody>'.PHP_EOL;
			echo '</table>'.PHP_EOL;
			echo '</fieldset>'.PHP_EOL;
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
		}
	}

	// Affichage des vues dépendantes de l'objet DB2
	sysViewDepInverse($cnxdb, $system_table_schema, $system_table_name);
	// recherche de dépendances Vues sur les indexs de type DDS (pour prise en compte des indexs surrogate)
	foreach($dataindexs as $index) {
		sysViewDepInverse($cnxdb, trim($index['LIBRARY']), trim($index['FILE']));
	}

	// Affichage des routines dépendantes de l'objet DB2
	sysRoutineDepInverse($cnxdb, $system_table_schema, $system_table_name);
	// recherche de dépendances Routines sur les indexs de type DDS (pour prise en compte des indexs surrogate)
	foreach($dataindexs as $index) {
		sysRoutineDepInverse($cnxdb, trim($index['LIBRARY']), trim($index['FILE']));
	}

	// Affichage des triggers dépendants de l'objet DB2
	sysTriggerDepInverse($cnxdb, $system_table_schema, $system_table_name);
	// recherche de dépendances Triggers sur les indexs de type DDS (pour prise en compte des indexs surrogate)
	foreach($dataindexs as $index) {
		sysTriggerDepInverse($cnxdb, trim($index['LIBRARY']), trim($index['FILE']));
	}

}
